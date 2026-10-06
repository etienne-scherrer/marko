# Devil's Advocate Review: lazy-can-middleware

## Critical (Must fix before building)

None.

## Important (Should fix before building)

1. **Task 001: `CanRouterTest` relies on autowiring the middleware.** `packages/authorization/tests/Feature/CanRouterTest.php::createAuthorizedRouter()` registers `GuardInterface` and `GateInterface` instances on a bare `Container` and lets the `Router` autowire `AuthorizationMiddleware`. Once the constructor takes two `Closure` params, autowiring cannot produce them and every router test fails with a container error. The task only says "existing tests updated". Fix: the helper must `$container->bind(AuthorizationMiddleware::class, fn () => new AuthorizationMiddleware(gate: fn () => $gate, guard: fn () => $guard))`, or reuse the module binding. Added to task 001.

2. **Task 001: the "no auth or session configured" test needs a precise setup or it proves nothing.** Unit-constructing the middleware with spy closures can't show that the *module binding* is lazy, and the eager resolution lived in the binding. Fix: build the middleware through `module.php`'s binding on a bare `Container` with no `AuthManager`, `AuthorizationConfig`, config repository, or session bound, so any eager `get()` throws. Then dispatch a non-`#[Can]` route, ideally through a real `Router` with the middleware global. That should also cover an unmatched (404) request, because `Router::dispatch()` runs global middleware for unmatched requests too. Clarified in task 001.

3. **Task 001: memoization semantics are unspecified.** Workers need to know whether a failed factory call is cached. Fix: memoize only on success, in nullable private properties. A throwing factory (for example, a misconfigured guard) then throws again on every `#[Can]` request instead of leaving a half-initialized singleton. Also, don't call the gate factory for guests: the guard check comes first, so a 401 never builds the Gate. Added to task 001.

4. **Task 002 and plan: the docs claim about sessions is wrong as written.** "Routes without `#[Can]` never build the gate, guard or session" is false at the app level, because `marko/session-file` and `marko/session-database` register `SessionMiddleware` globally and it still runs on every request. The guarantee only covers `AuthorizationMiddleware`: it doesn't resolve the Gate, `AuthManager`, the guard, or `AuthorizationConfig`. Fix: word the docs requirement and the success criterion so the claim is scoped to the authorization middleware. Updated task 002 and `_plan.md`.

## Minor (Nice to address)

- The constructor signature change breaks any app subclass or Preference of `AuthorizationMiddleware` (the class is non-final by design). It's acceptable pre-1.0, but the PR description should call it out.
- `@param Closure(): GateInterface` is only a docblock contract. The private `gate(): GateInterface` / `guard(): GuardInterface` accessors' return types make a wrong-typed factory fail loudly with a `TypeError`, which is enough. No extra validation is needed.
- `AuthManager::guard()` memoizes per name, so the Gate binding and the middleware's guard factory still share one guard instance. No change is needed, but the "same guard as the Gate" test should keep proving it.

## Questions for the Team

- Should the docs "Ordering with Sessions" section mention that the misconfiguration error now appears on the first `#[Can]` request rather than at boot, until option D lands? (The plan's Risks section implies yes. Task 002 now lists it.)
