# Plan: #[Can] 401s Carry the Stateless Guard's WWW-Authenticate Challenge

## Created
2026-10-06

## Status
completed

## Objective
Build every framework 401 for a guest in one place in `marko/authentication`, so a `#[Can]` route on a stateless (token) guard answers with `WWW-Authenticate: Bearer` exactly as `AuthMiddleware` already does.

## Related Issues
Closes #268

## Discovery Notes
- `AuthMiddleware` (marko/authentication) builds the stateless 401 inline: `new HttpException(401, 'Unauthorized.', ['WWW-Authenticate' => $guard->getChallenge()])`.
- `AuthorizationMiddleware` (marko/authorization) throws `HttpException::unauthorized('Unauthorized.')` with no challenge.
- `AdminAuthMiddleware` (marko/admin-auth, after #254) throws `HttpException::unauthorized('Unauthorized.')` for a JSON guest. Its guard is normally the session guard, but nothing stops an app from pointing it at a stateless one; using the shared factory keeps all three 401s identical.
- `HttpException` (marko/routing) is not final, carries headers, and is rendered through `ExceptionRenderer` by `instanceof HttpExceptionInterface`, so a subclass renders identically.
- marko/authorization already has `marko/authentication-token` in require-dev, so a router-level test with the real `TokenGuard` needs no new dependency.
- The ask is concrete (issue has approach + exit criteria); no clarification round.
- The worktree already contains `packages/authentication/src/Exceptions/UnauthenticatedException.php`, `packages/authentication/tests/Exceptions/UnauthenticatedExceptionTest.php` (covers all of task 001) and the two new `AuthMiddlewareTest` cases from task 002. Workers on 001/002 verify and complete them, and do not recreate them.
- `TokenGuard` reads the request only via `CurrentRequest`, which `TokenRequestMiddleware` sets. Router-level tests must register that middleware ahead of `AuthorizationMiddleware`, or every request looks like a guest (see task 003).
- Docs also live in `packages/docs-markdown/docs/guides/authentication.md`, which credits only `AuthMiddleware` with the challenge (task 005).

## Scope

### In Scope
- `Marko\Authentication\Exceptions\UnauthenticatedException extends HttpException` with `forGuard(GuardInterface $guard): self`
- `AuthMiddleware`, `AuthorizationMiddleware`, `AdminAuthMiddleware` throw it for a guest
- Unit tests + router integration test with the real `TokenGuard`
- Docs: authorization.md, authentication-token.md, authentication.md (and README if affected)

### Out of Scope
- Changing redirect behaviour of `AuthMiddleware` / `AdminAuthMiddleware`
- Making `AdminAuthMiddleware` skip its redirect for stateless guards

## Success Criteria
- [x] `#[Can]` + stateless guard guest → 401 with `WWW-Authenticate: Bearer` (unit + router test with real TokenGuard)
- [x] `#[Can]` + session guard guest → 401 without `WWW-Authenticate`
- [x] `AuthMiddleware` behaviour unchanged and uses the factory
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | UnauthenticatedException factory | - | completed |
| 002 | AuthMiddleware uses the factory | 001 | completed |
| 003 | AuthorizationMiddleware uses the factory (+ TokenGuard router test) | 001 | completed |
| 004 | AdminAuthMiddleware uses the factory | 001 | completed |
| 005 | Docs | 002, 003, 004 | completed |

## Architecture Notes
- A subclass of `HttpException` (rather than a static helper returning a plain `HttpException`) gives a catchable, discoverable type in one file with no new dependency; `ExceptionRenderer` renders it as any other `HttpException`.
- Client-facing message stays `Unauthorized.`; the guard name goes to the log-only `context`.

## Risks & Mitigations
- Tests asserting exact class `HttpException`: subclass still satisfies `toThrow(HttpException::class)`.
