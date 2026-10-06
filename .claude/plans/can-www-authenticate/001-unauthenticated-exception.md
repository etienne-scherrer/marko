# Task 001: UnauthenticatedException factory

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Authentication\Exceptions\UnauthenticatedException`, an `HttpException` subclass whose `forGuard()` factory builds the framework's 401 for a guest, adding the guard's `WWW-Authenticate` challenge when the guard is a `StatelessGuardInterface`.

## Context
- Related files: packages/authentication/src/Exceptions/, packages/routing/src/Exceptions/HttpException.php, packages/authentication/tests/Fixtures/StatelessFakeGuard.php
- Patterns to follow: static factories on exceptions; message/context split of HttpException

## Requirements (Test Descriptions)
- [x] `it builds a 401 HttpException for a guest`
- [x] `it uses Unauthorized. as the client-facing message`
- [x] `it adds the stateless guard's challenge as the WWW-Authenticate header`
- [x] `it sends no WWW-Authenticate header for a stateful guard`
- [x] `it names the guard in the log-only context`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- The class (`packages/authentication/src/Exceptions/UnauthenticatedException.php`) and its tests (`packages/authentication/tests/Exceptions/UnauthenticatedExceptionTest.php`) already exist in the worktree and cover every requirement above. Run them, confirm they pass along with lint and PHPStan, and fix anything that fails. Do not create a second copy.
