# Task 003: AuthorizationMiddleware uses the factory

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`AuthorizationMiddleware` throws `UnauthenticatedException::forGuard($guard)` for a guest on a `#[Can]` route, so a stateless default guard sends its challenge. Prove it with a unit test (fake stateless guard) and a router test with the real `TokenGuard`.

## Context
- Related files: packages/authorization/src/Middleware/AuthorizationMiddleware.php, packages/authorization/tests/Unit/Middleware/AuthorizationMiddlewareTest.php, packages/authorization/tests/Feature/CanRouterTest.php, packages/authentication-token/src/Guard/TokenGuard.php
- marko/authentication-token is already in marko/authorization's require-dev. Define fakes (stateless guard, token repository) inline in the authorization tests rather than importing other packages' test fixtures.

## Requirements (Test Descriptions)
- [x] `it sends the stateless guard's WWW-Authenticate challenge on a 401 for a guest`
- [x] `it sends no WWW-Authenticate header on a 401 for a guest on a stateful guard`
- [x] `it returns 401 with WWW-Authenticate: Bearer through the router for a Can route on the token guard without a token`
- [x] `it returns 401 with WWW-Authenticate: Bearer through the router for a Can route on the token guard with an unknown token`
- [x] `it returns 401 without WWW-Authenticate through the router for a guest on a session-style guard`
- [x] `it returns 200 through the router for a Can route on the token guard with a valid token` (positive control: proves the Authorization header actually reaches the guard)
- [x] Update the class docblock: a guest gets an `UnauthenticatedException` (401), which carries the guard's `WWW-Authenticate` challenge when the guard is stateless

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- **TokenGuard wiring (critical):** `TokenGuard` reads the header only through `Marko\AuthenticationToken\Http\CurrentRequest`, and only `Marko\AuthenticationToken\Middleware\TokenRequestMiddleware` sets it. The router does not. In the router tests, register `globalMiddleware: [TokenRequestMiddleware::class, AuthorizationMiddleware::class]` (in that order) and share ONE `CurrentRequest` instance between the container binding for `TokenRequestMiddleware` and the `TokenGuard`. Without this, every request is a guest and the unknown-token test passes vacuously.
- TokenGuard constructor: `(TokenRepositoryInterface $repository, CurrentRequest $currentRequest, ClockInterface $clock, UserProviderInterface $provider, string $name = 'token', ?EventDispatcherInterface $eventDispatcher = null)`. The repository looks tokens up by `hash('sha256', $rawToken)` via `findByToken()`. For the valid-token case, return a `PersonalAccessToken` with `tokenableId` set and `expiresAt = null`, and use `Marko\Testing\Fake\FakeUserProvider` (or an inline provider) that returns an `AuthorizableInterface` user for that id. Use any PSR clock (an inline one is fine).
- Send the token as `HTTP_AUTHORIZATION => 'Bearer <token>'` in the `Request` server array (see `createRouterRequest()` in CanRouterTest.php).
- Build the `Gate` with the same TokenGuard instance and define the ability as allowed, so the only 401 source is the guest check.
- Namespace and uniquely name every inline fixture class or helper function, because CanRouterTest.php already declares `createAuthorizedRouter` and similar helpers in `Marko\Authorization\Tests\Feature`.
- Keep `@throws ... HttpException ...` (UnauthenticatedException is a subclass), or add `UnauthenticatedException` to it.
