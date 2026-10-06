# Task 002: Remove unused ExpiredTokenException and InvalidTokenException

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`ExpiredTokenException` and `InvalidTokenException` are never thrown: the guard reports expired and invalid tokens through `TokenAuthenticationFailedEvent` and returns `null`. Delete both classes and their tests (pseudo-functionality).

## Context
- Related files: `packages/authentication-token/src/Exceptions/ExpiredTokenException.php`, `InvalidTokenException.php`, `tests/Exceptions/ExpiredTokenExceptionTest.php`, `tests/Exceptions/InvalidTokenExceptionTest.php`
- `TokenException` is their base class. Nothing else extends or throws it (`StatelessGuardException` extends `AuthException`), so it becomes dead code: delete it too (maintainer-default decision, recorded in the PR body).

## Requirements (Test Descriptions)
- [x] Delete `ExpiredTokenException`, `InvalidTokenException`, `TokenException` and the two exception test files (deletion task: verified by the package suite, PHPStan and a grep for leftover references, not by a new test asserting absence)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Deleted `ExpiredTokenException`, `InvalidTokenException`, their tests, and the base `TokenException`, which nothing else extended or threw once the other two were gone. A grep over `packages/*/src`, `packages/*/tests` and `tests/` finds no remaining references.
