# Task 004: Token guard: request holder, stateless contract, throwing stateful methods, failure event

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Add `Marko\AuthenticationToken\Http\CurrentRequest` (resettable holder) and global `TokenRequestMiddleware` that hands it the inbound request. Rework `TokenGuard`: reads the bearer token from `CurrentRequest`, configurable name, implements `StatelessGuardInterface` (challenge `Bearer`), re-resolves when the request changes, dispatches `TokenAuthenticationFailedEvent` once per request for invalid/expired tokens, and throws `StatelessGuardException` (extends `AuthException`) from `attempt()`, `login()`, `loginById()` and `logout()` pointing to `TokenManager`. Add `TokenGuardFactory`.

## Context
- Related files: packages/authentication-token/src/Guard/TokenGuard.php, tests/Guard/TokenGuardTest.php
- Patterns to follow: RequestCookieJar + QueuedCookiesMiddleware

## Requirements (Test Descriptions)
- [x] `it throws a stateless guard error from attempt, login, loginById and logout naming TokenManager`
- [x] `it reports the configured guard name`
- [x] `it implements StatelessGuardInterface with a Bearer challenge`
- [x] `it dispatches TokenAuthenticationFailedEvent with reason expired for an expired token without the token value`
- [x] `it dispatches TokenAuthenticationFailedEvent with reason invalid for an unknown token`
- [x] `it does not dispatch an event for a successful authentication or a missing token`
- [x] `it re-resolves the user when the current request changes`
- [x] `it treats a missing current request as a guest without dispatching an event` (CLI, queue jobs)
- [x] `it hands the request to CurrentRequest and clears it after the request, even when the pipeline throws` (TokenRequestMiddleware resets in `finally` so long-running workers never reuse a previous request's token)
- [x] `it never includes the plain-text token in ExpiredTokenException or InvalidTokenException` (both `forToken()` currently embed `$token` in `context`; drop it, e.g. take the token id or nothing, and invert the assertion in `tests/Exceptions/ExpiredTokenExceptionTest.php`)

## Interface Notes
- `StatelessGuardException` lives at `Marko\AuthenticationToken\Exceptions\StatelessGuardException` and extends `Marko\Authentication\Exceptions\AuthException`.
- `TokenGuardFactory::create(string $name, array $config, UserProviderInterface $provider): TokenGuard` resolves `TokenRepositoryInterface`, `CurrentRequest`, `ClockInterface`, `EventDispatcherInterface` from its own constructor.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
