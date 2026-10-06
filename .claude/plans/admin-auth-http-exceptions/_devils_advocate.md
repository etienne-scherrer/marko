# Devil's Advocate Review: admin-auth-http-exceptions

## Critical (Must fix before building)
None. The plan is small and correctly scoped. `Router::dispatch()` calls `withRoute()` before the pipeline runs, and `MiddlewarePipeline` catches `HttpExceptionInterface` at the depth it is thrown, so the mechanism the plan relies on works as described.

## Important (Should fix before building)

### I1. Task 001 does not say the existing unit tests must be rewritten
`packages/admin-auth/tests/Unit/Middleware/AdminAuthMiddlewareTest.php` has 8 tests that call `handle()` and assert on a returned 401/403 `Response`: lines 101, 151, 221, 241, 311, 350, 388 and 401. Some of them also assert `body() === 'Forbidden'` or `['error' => 'Unauthorized'|'Forbidden']`. Once the middleware throws, all 8 will error. The task only lists new tests, so a worker could add new tests next to the broken ones and leave duplicates behind.
**Fix:** Task 001 now says to convert these tests to `expect(fn () => ...)->toThrow(...)`, fold duplicates into the new requirements, and keep the pass-through and redirect tests unchanged.

### I2. Task 001: the factory methods cannot produce the planned exception
`HttpException::forbidden(string $message = '')` takes no `context`, and its default message is `HttpStatus::reasonPhrase(403)`, which is `"Forbidden"` without a period. The success criterion `{"message":"Forbidden."}` and the "permission key in context" requirement therefore need the full constructor. The task also does not mention cleanup that phpcs/PHPStan will catch: after the change the `JsonException` import and the `@throws ...|JsonException` docblock no longer apply.
**Fix:** Task 001 now gives the exact construction to use (`new HttpException(statusCode: 403, message: 'Forbidden.', context: ...)` and `HttpException::unauthorized('Unauthorized.')`) and lists the import/docblock cleanup.

### I3. Task 002: container wiring is not specified, and the CanRouterTest `module.php` pattern will fail
`PermissionRegistryInterface` has no binding in `packages/admin-auth/module.php` or anywhere else in `src/`. If the worker copies `createUnconfiguredRouter()` from CanRouterTest, or relies on autowiring, the container throws `BindingException::noImplementation`. The task also does not say how to attach the middleware. Production uses route-level `#[Middleware(AdminAuthMiddleware::class)]`, not global middleware.
**Fix:** Task 002 now lists the explicit container instances (`GuardInterface` via `FakeGuard`, `AdminConfigInterface` via a stub, `PermissionRegistryInterface` via `new PermissionRegistry()`). It also says to attach the middleware with `RouteDefinition(middleware: [AdminAuthMiddleware::class])`, use a unique `Marko\AdminAuth\Tests\Feature` namespace, and give fixture classes names that don't clash with the unit test's `TestController*` / `StubAdminConfig`.

### I4. Task 003: `admin-api.md` will be wrong after this change
`packages/docs-markdown/docs/packages/admin-api.md` line 6 says all responses follow a `{data, meta}` / `{errors}` envelope, and line 20 says "Unauthenticated requests receive a 401 JSON response". After this change, middleware denials are `{"message":"..."}`, and requests without a JSON `Accept` header get a redirect. Only `admin-auth.md` is in scope today.
**Fix:** `admin-api.md` was added to task 003, with a requirement to correct both statements.

## Minor (Nice to address)
- **Negotiation edge (001/002):** `Request::wantsJson()` reads only `Accept`. `ExceptionRenderer::wantsJson()` falls back to `Content-Type` when `Accept` is absent. A `POST` with `Content-Type: application/json` and no `Accept` is therefore redirected with a 302 instead of getting a JSON 401. This matches the old behaviour and `AuthMiddleware`, but it is worth one docs sentence.
- **Behaviour change (001):** `Accept: application/vnd.api+json` used to get a redirect (the old check was `str_contains($accept, 'application/json')`). It now gets a JSON 401. A unit test that pins this would document the change.
- **HTML 403 (002/003):** the 403 changes from a plain-text `Forbidden` body to the full `ExceptionRenderer` HTML page. This is intended, but the PR description should mention it.
- **Composer test (001):** extend the existing `has valid composer.json ...` assertion chain instead of adding a separate test, to match the file's style. Either way is fine.

## Questions for the Team
- **No production binding for `PermissionRegistryInterface`:** nothing in `packages/*/src` or any `module.php` binds it. `AdminAuthMiddleware` is resolved through the container on every admin route, so a real app should get a `BindingException`, unless the app binds it itself. This is out of scope for #254. Should it be filed as a separate issue?
- **Envelope mismatch in admin-api:** admin-api controllers answer errors with `ApiResponse` (`{"errors":[{"message":...}]}`), while middleware denials on the same routes will be `{"message":"..."}`. Is that acceptable, or should admin-api later get an `ExceptionRenderer` preference for its routes?
