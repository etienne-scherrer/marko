# Task 003: TestClient multi-file upload 422 test

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove at the routing level that a multi-file upload sent with `TestClient` and validated with `photos.*` returns 422 with per-file error keys.

## Context
- Related files: packages/testing/tests/fixtures/http-app (add a gallery controller and a `vendor/marko/validation` stub), packages/testing/composer.json (require-dev marko/validation)
- Patterns to follow: TestClientUploadsTest

## Requirements (Test Descriptions)
- [x] `it returns 422 with an error for each invalid photo keyed by index`
- [x] `it accepts a multi-file upload when every photo is valid`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Add `GalleryController` (`POST /gallery`) to the fixture app and `TestClientValidationTest`.

Gotchas (verified against current code):
- **Merge files into the data.** `$request->post()` contains no uploads. Validate `[...$request->post(), ...$request->files()]` with `['photos' => 'required|array', 'photos.*' => 'image|max_size:...']`, mirroring `packages/validation/tests/Integration/FileValidationRoutingTest.php`.
- **Ask for JSON.** Multipart requests carry no JSON `Accept`/`Content-Type`, so `ExceptionRenderer` would render HTML. Send `->withHeader('Accept', 'application/json')`.
- **Don't assert error keys with `assertJsonPath`.** `TestResponse::resolvePath()` splits on `.`, so `errors.photos.1` looks for `errors['photos']['1']`, while the real key is the literal `photos.1`. Use `expect($response->json('errors'))->toBe([...])` (or `array_keys`), after `->assertUnprocessable()`.
- **Fixture stub must bind the validator.** Fixture `vendor/marko/*/module.php` files are hand-written (see `vendor/marko/clock/module.php`). Add `vendor/marko/validation/composer.json` (type `marko-module`, `extra.marko.module: true`) and a `module.php` that binds `ValidatorInterface::class => Validator::class`.
- **Real image bytes.** The `image` rule sniffs contents. Write a 1x1 PNG with the test's own `uploadFixture()`-style helper instead of importing `Marko\Validation\Tests\Support\TestUploads` across packages; use a text file for the invalid photo.
