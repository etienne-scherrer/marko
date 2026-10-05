# Devil's Advocate Review: can-authorization

## Critical (Must fix before building)

1. **Task 002 is not independent of Task 001.** Task 002 adds an `AuthorizationMiddleware` binding closure in `module.php` (`new AuthorizationMiddleware(gate: ..., guard: ...)`) to pin the guard to `authorization.default_guard`, and registers it as a singleton. That closure must target the constructor that Task 001 produces (scalars removed). If both run in parallel, 002 is written against the old signature and its guard test has to make assumptions about 001's internals. Fix: Task 002 now depends on 001.

## Important (Should fix before building)

1. **Task 002: "builds the middleware with the guard configured for authorization" is not observable as written.** `$guard` is a private constructor property, so a worker will either reflect into privates or write a test that proves nothing. Fix: specify a behavioral test. Bind an `AuthManager` (or fake) where the default guard is authenticated and the `authorization.default_guard` guard is not. Resolve the middleware through the module binding and call `handle()` with a request carrying a `#[Can]` route. Expect 401, which shows that the authorization guard was consulted.
2. **Task 001: no requirement covers a request without a matched route.** Global middleware can be invoked with a `Request` where `controller()`/`action()` are null (direct pipeline use, tests, future fallback handlers). The pass-through branch needs a test so it doesn't become a `ReflectionMethod(null, ...)` TypeError. The existing `CanRouterTest.php` also has a plain-text 401 test that the task requirements don't list. Both were added to Task 001.

## Minor (Nice to address)

- `ReflectionClass::getAttributes()` does not inherit class-level attributes from parent controllers, so `#[Can]` on an abstract base controller will silently not apply. Either document this or walk parents. Note it in the Task 003 docs.
- If an app already lists `AuthorizationMiddleware` as route middleware, it now runs twice (global + route), which means double gate evaluation. Harmless but wasteful. A docs note in 003 is enough.
- `Request::controller()` is the route-declared class. For Preference-replaced controllers, attributes on the replacement class are ignored. This is acceptable but undocumented.

## Questions for the Team

- **Eager construction on every request.** `AuthManager` requires `SessionInterface` in its constructor. Once the middleware is global, every request builds `GateInterface` -> `AuthManager`. An app with `marko/authorization` but no session driver will now fail on every route, including routes without `#[Can]`. Before this change, only authorization-using code failed. The plan accepts this ("fails loudly"). Confirm that this is intended, or decide whether the middleware should resolve the gate/guard lazily, only once a `#[Can]` is found.
