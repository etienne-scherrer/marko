# Devil's Advocate Review: file-validation-rules

## Critical (Must fix before building)

### C1. Shared contracts are unspecified for parallel workers (tasks 002-006, 008)
Tasks 002-005 run in parallel after 001, but the task files are one-liners: no class names, no constructor signatures, no message texts, no exception type. Task 006 has to construct the rules from strings, task 007 asserts on errors, and task 008 documents the messages. Each worker would invent its own (e.g. `Mimes(array $types)` vs `Mimes(string ...$types)`), so 006 would break on integration.
**Fix:** add a "Shared Contracts" section to `_plan.md` that pins class names, constructor signatures, message texts and the empty/missing-parameter behavior. Then reference it from every task.

### C2. Rules must not depend on routing, so which exception do they catch? (tasks 002-004)
`UploadedFile::mimeType()` / `guessExtension()` throw `Marko\Routing\Exceptions\UploadedFileException`. They throw not only for failed or moved uploads but also for an *unreadable* temp file, which `isValid()` does not catch. `marko/validation` only requires `marko/core`, so validation `src/` must not reference the routing exception. `UploadedFileException extends Marko\Core\Exceptions\MarkoException`, and the core interface already documents `@throws MarkoException`.
**Fix:** rules check `isValid()` first, then catch `MarkoException` around `mimeType()`/`guessExtension()` and return `false`. This prevents an unreadable temp file from becoming a 500. Added to 002/003 and the shared contracts.

### C3. Test fixture strategy is undefined, and Pest helper functions collide (tasks 002-007)
Every type-rule test needs a real file on disk with real magic bytes, because `finfo` reads it. Parallel workers will each write a top-level `function makeUpload()` helper. Pest loads all test files into one process, so two identically named functions in the same namespace cause a fatal "Cannot redeclare". Workers could also reach for a mock of the interface, which would not prove that client metadata is ignored.
**Fix:** tests build real `Marko\Routing\Http\UploadedFile` instances (validation already require-devs routing) over temp files with known magic bytes, and clean them up in `afterEach`. Helpers must be closures, or functions in a namespace unique to the test file. The fixture bytes are pinned in `_plan.md`.

## Important (Should fix before building)

### I1. Task 007 is placed in the wrong package (task 007)
`marko/routing` does not require-dev `marko/validation`; only the reverse is true. A routing test that throws `ValidationException` relies on monorepo root autoloading and breaks the package's own dependency graph. The pattern file's `bootRouter()` helper is also a namespaced function local to `Marko\Routing\Tests\HttpExceptionHandling`, so it can't be reused.
**Fix:** put the test in `packages/validation/tests/Integration/FileValidationRoutingTest.php` under its own namespace, and copy (don't import) the boot pattern. Inject files via `new Request(server: [...], files: [...])`.

### I2. RuleParser parameter handling for the new rules is unspecified (task 006)
`mimes` with no colon gives `[]`, and `mimes:` gives `['']`. Existing rules default missing parameters to `0`, so `max_size` with no parameter would silently mean 0 KB and reject everything. That violates "loud errors".
**Fix:** trim the parameters, drop blanks and lowercase `mimes`/`mimetypes` entries. Throw `InvalidArgumentException` when `max_size`/`min_size` has a missing or non-numeric parameter. Pass `mimes`/`mimetypes` through so the constructors throw on an empty list.

### I3. message() must not touch the file (tasks 002-005)
`Validator` calls `$rule->message($field, $value)` after a failure. If a message calls `mimeType()` (for example "got image/svg+xml"), it re-throws for invalid uploads.
**Fix:** messages use only the rule's own configuration and `instanceof` checks.

### I4. Max/Min/Between need to detect files in message() before the generic fallbacks (task 005)
`passes()` already returns `false` for objects. The work is in `message()`, which must check `instanceof UploadedFileInterface` first. Spelled this out so the worker doesn't add a size comparison to `max`. The plan says these must fail, which keeps size semantics in one place.

### I5. Size semantics not pinned (task 004)
Inclusive or exclusive bounds, KB = 1024 bytes, and int or float parameters were not specified. "at the minimum size passes" was missing as a boundary test.
**Fix:** bounds are inclusive (`size() <= max * 1024`, `size() >= min * 1024`), the parameter is `int|float`, and a min-boundary test was added.

### I6. Task 001 contract should match the interface already present in the worktree (task 001)
`packages/core/src/Contracts/UploadedFileInterface.php` already exists, and routing `UploadedFile` already implements it. The task did not list the methods. Pinned the method list so 002-005 can rely on it.

## Minor (Nice to address)
- `image` excludes avif/heic/bmp, which libmagic detects and `UploadedFile::EXTENSIONS` knows about. This is a product decision; see Questions.
- An SVG may be sniffed as `image/svg+xml`, `text/xml` or `text/plain` depending on the libmagic version. The "fails image for an svg" test passes in every case, but it shouldn't assert a specific sniffed type.
- `max:N` on an *array* of files still counts items. That's correct, but worth a sentence in the docs (task 008).
- In the documented `[...$request->post(), ...$request->files()]` spread, a file field silently overrides a same-named post key. Worth noting in the docs.
- New rules should be `readonly class`, matching `Max`/`Min`/`Between`, to keep PHPStan and style consistent.

## Questions for the Team
- Should `image` accept `image/avif` and `image/heic`? The issue says jpeg/png/gif/webp only.
- Should an upload error (e.g. `UPLOAD_ERR_INI_SIZE`) get a more specific message, such as "exceeds the server upload limit"? Today every rule reports the generic "must be a successfully uploaded file".
