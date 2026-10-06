# Task 002: AuthMiddleware uses the factory

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Replace both inline 401 constructions in `AuthMiddleware` with `UnauthenticatedException::forGuard()`. Redirect logic for stateful guards is unchanged.

## Context
- Related files: packages/authentication/src/Middleware/AuthMiddleware.php, packages/authentication/tests/Unit/Middleware/AuthMiddlewareTest.php

## Requirements (Test Descriptions)
- [x] `it throws an UnauthenticatedException carrying the challenge for a stateless guard`
- [x] `it throws an UnauthenticatedException without a challenge for a JSON request on a stateful guard`
- [x] existing AuthMiddleware tests still pass (redirect, wantsJson, pass-through)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Both new tests already exist in `packages/authentication/tests/Unit/Middleware/AuthMiddlewareTest.php` (around line 314). Check whether `AuthMiddleware.php` already uses the factory. If it doesn't, make the change so those tests pass. Do not duplicate the tests.
- Both guest paths (stateless, and stateful-JSON or `redirectTo === null`) become `throw UnauthenticatedException::forGuard($guard)`. The stateless branch must still come before the redirect check.
