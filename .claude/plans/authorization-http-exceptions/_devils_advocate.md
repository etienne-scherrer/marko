# Devil's Advocate Review: authorization-http-exceptions

## Critical (Must fix before building)

1. **Task 002 breaks `CanRouterTest`, owned by task 004.** `packages/authorization/tests/Feature/CanRouterTest.php` asserts the old bodies: `'Forbidden'` (line 177), `'Unauthorized'` (line 195), and `'{"error":"Unauthorized"}'` (line 204). Once task 002 lands, the authorization suite goes red, and task 004 cannot start until it is green again. Fix: task 002 updates those existing assertions to the renderer output. Task 004 only adds new tests.

2. **Removing the `instanceof TokenGuard` branch (task 003) makes API routes redirect.** `#[Middleware(AuthMiddleware::class)]` (`packages/routing/src/Attributes/Middleware.php`) accepts only class strings. The container therefore always builds `AuthMiddleware` with `redirectTo: '/login'`. The task note says "API route groups pass `redirectTo: null`", but no route-group or middleware-argument mechanism exists. Every token-guard route (see the `build-a-rest-api.md` tutorial) would return 302 → /login instead of 401. Fix: keep the token-guard short-circuit, but have it throw `HttpException::unauthorized()` instead of building JSON. The renderer still picks JSON or HTML.

## Important (Should fix before building)

3. **Task 001 does not define the constructor contract.** The current `AuthorizationException` declares its own `private readonly $context`/`$suggestion` plus `getContext()`/`getSuggestion()`. If those stay while it extends `MarkoException`, it shadows the parent's private properties. Callers use named args (`Gate`, `PolicyRegistry`, tests, and `admin-auth.md` does `new AuthorizationException('Cannot cancel orders')`). Fix: specify the signature, pass context/suggestion to the parent, and redeclare nothing.

4. **Task 001: the `PolicyException` contract and the touched call sites are missing.** It needs factories for `PolicyRegistry::register()` (duplicate) and `Gate::callPolicy()` (missing method). It also needs `@throws` updates in `GateInterface`, `Gate` and `PolicyRegistry`. Existing tests must change too: `AuthorizationExceptionTest` (constructor and `missingPolicy` tests), `PolicyRegistryTest:151` (duplicate → `PolicyException`), and the misnamed `PolicyRegistryTest:145`.

5. **Task 002: the existing unit tests assume responses.** `AuthorizationMiddlewareTest` lines 132–227 and the cache test at line 264 assert `statusCode()` on returned responses. They must be rewritten to expect thrown exceptions. The `@throws JsonException` docblock should become `@throws HttpException|AuthorizationException`.

6. **Task 004: the integration route must not carry `#[Can]`.** Otherwise the middleware throws first and `Gate::authorize()` is never exercised. Specify a route with no `#[Can]` whose controller calls `GateInterface::authorize('view-admin')`. A guest also gets 403 here, because the Gate has no 401 notion. Adding the route also changes `DiscoveryCacheTest`'s route snapshot. It compares cold and warm, so it should still pass, but the worker should run it.

7. **Task 005 misses stale docs.** `authentication.md:418` (`{"error": "Unauthorized"}`), `admin-auth.md:116` (`new AuthorizationException(...)`), `roadrunner-state-leaks.md:49` (`PolicyRegistry` throws `AuthorizationException` → `PolicyException`), and `authorization.md:143` (old JSON bodies).

## Minor (Nice to address)

- The 401 body is `{"message":"Unauthorized"}` (reason phrase, no period) while the 403 body is `{"message":"Forbidden."}`. The punctuation is inconsistent.
- `AuthorizationMiddleware` ignores `#[Can]` entity instances and passes only the class string. That is unchanged and out of scope.
- `admin-auth`'s `AdminAuthMiddleware` still builds `{"error":...}` responses, so error shapes stay mixed across packages (out of scope per the plan).

## Questions for the Team

- Should `AuthMiddleware` also skip the redirect when the request wants JSON (`ExceptionRenderer::wantsJson()`)? That would fix session-guard AJAX calls getting a 302, but it changes current behaviour.
- When #232 removes `TokenGuard`, what replaces the token-guard short-circuit in `AuthMiddleware`?
