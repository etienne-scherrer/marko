# Task 004: Fixture app

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Fixture project at `packages/testing/tests/fixtures/http-app`, following `packages/roadrunner/tests/Fixtures/app`. (The `marko/routing` require is owned by task 003; do not edit composer.json here.)

## Context
- Related files: packages/roadrunner/tests/Fixtures/app, /.gitignore, tests/FixtureTrackingTest.php
- `tests/fixtures` (lowercase) already exists; a `tests/Fixtures` sibling collides on case-insensitive filesystems.
- Root `.gitignore` ignores `vendor/` at any depth and only re-includes `!/packages/*/tests/Fixtures/**/vendor/` (capital F). The lowercase path is NOT covered, so the fixture's `vendor/marko/*/module.php` stubs would be untracked and missing in CI. On macOS (core.ignorecase) it may appear to work locally.

## Requirements (Test Descriptions)
- [x] routes: JSON in/out, form post, redirect, cookie set/read, global middleware, auth-protected route, request-scoped ResettableInterface service, request echo, file upload
- [x] HEAD and OPTIONS echo routes via fixture-local `Route` subclasses (Marko has no Head/Options attributes; unmatched routes 404 before global middleware, so verbs are otherwise unobservable)
- [x] fixture boots through `Application::boot()`
- [x] `.gitignore` gains `!/packages/*/tests/fixtures/**/vendor/`, and `tests/FixtureTrackingTest.php` scans `packages/*/tests/fixtures` as well as `Fixtures`; the tracking test passes

## Acceptance Criteria
- Fixture used by tasks 005-007

## Implementation Notes
- Session config `path` must be per-process under `sys_get_temp_dir()` (see roadrunner fixture `config/session.php`); tests that use it clean the directory up.
- No fixture file name may end in `Test.php` (Pest would collect it).
- `vendor/autoload.php` delegates to the monorepo autoloader like the roadrunner fixture (adjust the `dirname()` depth).
