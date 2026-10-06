# Plan: File Validation Rules

## Created
2026-10-05

## Status
completed

## Objective
Give `marko/validation` real rules for uploaded files (`file`, `image`, `mimes`, `mimetypes`, `max_size`, `min_size`) and make `max`/`min`/`between` fail on files with a message pointing to the size rules.

## Related Issues
Closes #231

## Discovery Notes
- `Marko\Routing\Http\UploadedFile` (from #174) exposes `size()`, `error()`, `isValid()`, `clientMediaType()`, sniffed `mimeType()` and `guessExtension()`.
- `marko/validation` only require-devs `marko/routing`; rules live in `src/Rules` and are registered in `RuleParser::parseRule()`.
- `Max`/`Min`/`Between` return `false` for objects with a numeric message, so `max:2048` rejects every upload.
- `Validator` has no `photos.*` wildcard support; arrays of files work with `array`/`max:N` (item count) only. Wildcards are a separate ticket.
- Dependency direction: option (b) from the issue. `Marko\Core\Contracts\UploadedFileInterface` lives in `marko/core` and is implemented by routing's `UploadedFile`; validation depends only on core.

## Scope

### In Scope
- `UploadedFileInterface` in core; routing `UploadedFile` implements it
- Rules `File`, `Image`, `Mimes`, `MimeTypes`, `MaxSize`, `MinSize` registered in `RuleParser`
- File-aware failure messages in `Max`, `Min`, `Between`
- Routing-level test proving a 422 with field-keyed errors
- Docs: validation.md (file rules, passing files) and a link from routing.md

### Out of Scope
- `photos.*` wildcard validation
- A Request/validator merge helper (the array spread is documented instead)

## Success Criteria
- [x] All six rules exist with tests for pass, fail, non-file, upload error and (type rules) lying client metadata
- [x] `max`/`min`/`between` on a file fail with a message naming `max_size`/`min_size`
- [x] 422 `ValidationException` errors keyed by field through `Router::handle()`
- [x] composer.json files reflect the dependency direction
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | UploadedFileInterface in core, implemented by routing UploadedFile | - | completed |
| 002 | file and image rules | 001 | completed |
| 003 | mimes and mimetypes rules | 001 | completed |
| 004 | max_size and min_size rules | 001 | completed |
| 005 | max/min/between file messages | 001 | completed |
| 006 | Register rules in RuleParser | 002, 003, 004, 005 | completed |
| 007 | Routing-level 422 test (lives in validation tests) | 006 | completed |
| 008 | Docs and README | 006 | completed |

## Architecture Notes
- Type rules use only the sniffed `mimeType()` / `guessExtension()`, never `clientMediaType()` or the client filename.
- Every file rule checks `isValid()` first, because `mimeType()` throws for failed or moved uploads.
- `image` deliberately excludes `image/svg+xml`.
- Sizes are kilobytes (1024 bytes).

## Implementation Deviations
- **No try/catch around `mimeType()`/`guessExtension()`.** The review proposed failing validation when the temp file is unreadable. A successful upload whose temp file is missing is a server fault, not bad input, so the routing `UploadedFileException` propagates (loud error, 500) instead of being reported as "must be an image". The rules declare `@throws MarkoException`.
- **Messages.** Failed uploads get a reason-specific message instead of one generic text: `The $field field must be a file.` (not a file), `The $field file has already been moved.`, `The $field file is larger than the server allows.` (`UPLOAD_ERR_INI_SIZE`/`UPLOAD_ERR_FORM_SIZE`), otherwise `The $field file failed to upload.`. `image`: `The $field field must be an image (JPEG, PNG, GIF or WebP).` `max`/`min`/`between` on a file: `The $field field is a file: use max_size:N to limit its size in kilobytes.` (and the `min_size` / `min_size:N|max_size:N` equivalents).
- **Shared base class.** The "check it is a valid upload first" logic lives in `AbstractFileRule` (no traits); subclasses implement `passesFile()` and `fileMessage()`.
- **Fixtures.** Tests build real uploads through `Marko\Validation\Tests\Support\TestUploads` (autoloaded class, no global helper functions).

## Shared Contracts (as planned; see Implementation Deviations for what changed)

### Interface (task 001, `Marko\Core\Contracts\UploadedFileInterface`)
`clientFilename(): string`, `clientMediaType(): string`, `size(): int` (bytes), `error(): int`, `isValid(): bool`, `isMoved(): bool`, `moveTo(string $targetPath): void`, `stream(): mixed`, `contents(): string`, `mimeType(): string`, `guessExtension(): ?string`. Throwing methods document `@throws Marko\Core\Exceptions\MarkoException` (routing's `UploadedFileException` extends it).

### Rule rules
- Validation `src/` never references any `Marko\Routing\*` class; it depends only on `UploadedFileInterface` and `MarkoException` from core.
- Every file rule's `passes()`: `false` unless `$value instanceof UploadedFileInterface && $value->isValid()`. Calls to `mimeType()`/`guessExtension()` are wrapped in `try { … } catch (MarkoException) { return false; }` (an unreadable temp file must fail validation, not 500).
- `message()` never calls `mimeType()`, `guessExtension()` or any other file I/O; it uses only the rule's configuration and `instanceof`/`isValid()` checks.
- New rules are `readonly class … implements RuleInterface` in `Marko\Validation\Rules`.

### Classes, constructors, messages
| Rule string | Class | Constructor | Failure message (`$field` interpolated) |
|---|---|---|---|
| `file` | `File` | none | `The $field field must be a successfully uploaded file.` |
| `image` | `Image` | none | not a valid upload: the `file` message; otherwise `The $field field must be an image (jpeg, png, gif or webp).` |
| `mimes:jpg,png` | `Mimes` | `string ...$extensions` (throws `InvalidArgumentException` when empty; lowercased; `jpeg` matches `jpg` and vice versa) | not a valid upload: the `file` message; otherwise `The $field field must be a file of type: jpg, png.` (list joined with `, `) |
| `mimetypes:image/*,application/pdf` | `MimeTypes` | `string ...$types` (throws `InvalidArgumentException` when empty; lowercased; `type/*` matches any subtype) | not a valid upload: the `file` message; otherwise `The $field field must be a file of type: image/*, application/pdf.` |
| `max_size:2048` | `MaxSize` | `int\|float $kilobytes` | not a valid upload: the `file` message; otherwise `The $field field must not be larger than 2048 kilobytes.` |
| `min_size:10` | `MinSize` | `int\|float $kilobytes` | not a valid upload: the `file` message; otherwise `The $field field must be at least 10 kilobytes.` |
| `max` on a file | `Max` | unchanged | `The $field field is a file: use max_size:N (kilobytes) to limit its size.` |
| `min` on a file | `Min` | unchanged | `The $field field is a file: use min_size:N (kilobytes) to require a minimum size.` |
| `between` on a file | `Between` | unchanged | `The $field field is a file: use min_size:N and max_size:N (kilobytes) to bound its size.` |

Size bounds are inclusive: `size() <= kilobytes * 1024` and `size() >= kilobytes * 1024`.

### Test fixtures
- Tests use real `Marko\Routing\Http\UploadedFile` instances (validation require-devs routing) over temp files written with `tempnam(sys_get_temp_dir(), 'mk')` + `file_put_contents`, deleted in `afterEach`. Do not mock the interface for type rules: the lying-metadata tests need real `finfo` sniffing.
- Magic bytes: PNG = `base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==')`, GIF = `"GIF89a\x01\x00\x01\x00\x00\x00\x00;"`, PDF = `"%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n"`, text = `"hello world\n"`, SVG = `'<svg xmlns="http://www.w3.org/2000/svg"></svg>'` (never assert the SVG's sniffed type, which varies by libmagic).
- Upload errors: `new UploadedFile($path, 'a.png', 'image/png', 100, UPLOAD_ERR_INI_SIZE)`.
- Never declare top-level named helper functions in the global or a shared test namespace (Pest loads all files in one process: "Cannot redeclare"). Use closures, or give each test file its own namespace.

## Risks & Mitigations
- finfo results vary by libmagic version: tests use well-known magic bytes (PNG, GIF, PDF, plain text).
