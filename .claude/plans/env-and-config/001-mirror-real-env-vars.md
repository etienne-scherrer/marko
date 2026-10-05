# Task 001: Mirror real env vars in EnvLoader

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`EnvLoader::load()` copies `getenv()` into `$_ENV` (without overwriting) before the `.env` existence check, so config files reading `$_ENV` see real environment variables even when `variables_order` lacks `E`.

## Context
- Related files: packages/env/src/EnvLoader.php, packages/env/tests/Unit/EnvLoaderTest.php
- Simulate `variables_order` without `E` by clearing `$_ENV` and using `putenv()`.
- Mirror with `getenv()` (no arguments). Skip keys already in `$_ENV`.
- `Application::initialize()` always calls `EnvLoader::load()` in the monorepo, so tests must snapshot and restore the WHOLE `$_ENV` array and clear every `putenv()` key in `afterEach` to avoid leaking state into other tests.

## Requirements (Test Descriptions)
- [x] `it mirrors real environment variables into $_ENV when no .env file exists`
- [x] `it does not overwrite values already present in $_ENV when mirroring`
- [x] `it keeps a real environment variable over the same key in .env`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
