# Devil's Advocate Review: validation-wildcard-rules

## Critical (Must fix before building)

1. **Task 003: `assertJsonPath('errors.photos.1', ...)` cannot work.** `TestResponse::resolvePath()` splits on `.`, so it looks for `errors['photos']['1']`, but the error keys are the literal strings `photos.1`. Tests must read `->json('errors')` and assert with `expect(...)->toBe([...])` / `array_keys`.
2. **Task 003: the 422 body is HTML unless the request asks for JSON.** `ExceptionRenderer::wantsJson()` checks `Accept`, and falls back to `Content-Type` only when `Accept` is missing. A multipart upload has neither as JSON, so the test must call `->withHeader('Accept', 'application/json')`.
3. **Task 003: the controller has to merge files into the data.** `$request->post()` has no uploads in it. Follow `FileValidationRoutingTest::AvatarController`: `validateOrFail([...$request->post(), ...$request->files()], ...)`.

## Important (Should fix before building)

4. **Task 001: the de-duplication note contradicts the pre-written test.** The note says the scalar-at-wildcard message is "de-duplicated across keys". `WildcardRulesTest` ("reports a scalar at a wildcard position under every key that reaches it") expects one message per rule key under the same path. Fix: one message per (path, rule key) pair.
5. **Task 001: rule typos would pass silently when a wildcard expands to nothing.** If rules are parsed per concrete key, `'photos.*' => 'imagee'` or `max_size` with no number never reaches `RuleParser` when `photos` is empty, so no exception is thrown. That breaks the loud-errors principle. Parse once per rule key, before expansion. Also cheaper: one parse per key instead of one per item.
6. **Task 001: "scalar" is too narrow.** In practice this case is a single `UploadedFile` object at `photos` (form field `photos` instead of `photos[]`). Any non-array, non-empty value (objects included) must produce the "must be an array" error. Add a test.
7. **Task 001 → 002 contract.** Task 002 needs each concrete key's matched wildcard indexes. Task 001 should define `expand()` to return `concrete key => list<int|string> indexes`, so Task 002 doesn't have to rework the expansion.
8. **Task 001: the requirement list is out of sync with the existing test file.** `packages/validation/tests/Unit/Validation/WildcardRulesTest.php` and `src/Validation/DataPath.php` already exist in the worktree. The file has two tests the task doesn't list ("reports a scalar ... under every key", "treats a leading wildcard as every top-level key").
9. **Task 002: unspecified message and types.** After `forWildcardIndexes()`, the message must name the resolved field (`items.0.password_confirmation`), not `items.*...`. Indexes can be string keys (associative arrays), so the type is `list<int|string>` and needs a PHPDoc for PHPStan. `Same`/`Different` are `readonly`, so return a new instance.
10. **Task 003: fixture stub must carry the binding.** Fixture `vendor/marko/*/module.php` files are hand-written copies (see the clock stub), so the new `vendor/marko/validation/module.php` must bind `ValidatorInterface => Validator`. Upload bytes must be real image bytes (the `image` rule sniffs contents). Inline a 1x1 PNG rather than depending on `Marko\Validation\Tests\Support\TestUploads` across packages.

## Minor (Nice to address)

- `Confirmed`/`Same`/`Different` now resolve dot paths, so a flat data key literally containing `.` (`'user.email'`) is no longer found. Mention it in the docs or changelog (Task 004).
- `isEmpty()` treats whitespace-only strings as empty. A wildcard over `'   '` expands to nothing, consistent with `''`.
- Rule keys that PHP turns into ints (e.g. `'0'`) still hit `validateField(string $field)` with a TypeError. This was already the case before this plan.

## Questions for the Team

- A misconfigured `same:items.*.x` on a plain key throws only when there is data to iterate. With an empty parent the rule never resolves, so nothing throws. Should the wildcard count be checked once per rule key regardless of data?
