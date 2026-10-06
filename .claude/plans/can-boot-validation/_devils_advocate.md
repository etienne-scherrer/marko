# Devil's Advocate Review: can-boot-validation

## Critical (Must fix before building)

1. **Boot-callback ordering breaks custom guard drivers (006).** Guard drivers are registered in module `boot` callbacks (`GuardDriverRegistry::extend`, documented in `docs/packages/authentication.md` "Guard Drivers" with a `jwt` example in an app module). `Application::boot()` runs boot callbacks in topological module order, and nothing sequences an app module before `marko/authorization`. Only `marko/authentication-token` declares `before: marko/authorization`. A check inside authorization's own `boot` callback therefore runs before an app's `extend('jwt', ...)` and fails every app that follows the documented pattern with `unknownGuardDriver`. Core has no post-boot hook (`ModuleManifest` has only `boot`). **Fix:** new task 008 adds a core `ApplicationBooted` event, dispatched after all module boot callbacks. The check runs in an authorization observer of that event instead of in `boot`. 006 now depends on 008.

2. **Resolving the `GateInterface` singleton at boot breaks `actingAs()` (004).** `GateInterface` is a singleton whose binding captures `$authManager->guard(...)` when it is built. `TestClient::actingAs()` (packages/testing/src/Http/TestClient.php:332) calls `AuthManager::useGuard()` after boot. Today the Gate is built lazily on the first `#[Can]` request, so it picks up the fake guard. If the validator builds the Gate at boot, the Gate keeps the boot-time `SessionGuard`, the middleware's guard (resolved later through `AuthManager`) is the fake, `check()` passes, and `Gate::allows()` sees no user, so the result is a 403. **Fix:** the validator builds the guard through `AuthManager::guard()` (which `useGuard` later replaces) and resolves the Gate's own collaborators (`PolicyRegistry`, `AuthorizationConfig`). It never resolves `GateInterface`. 004 and 006 now require regression tests.

## Important (Should fix before building)

3. **`CanRouteCacheContributor` is redundant and stores dead data (005).** `CliKernel::LIVE_DISCOVERY_COMMANDS` makes `discovery:cache` boot live, so the boot-time check already fails `discovery:cache` before `DiscoveryCompiler` runs. Nothing reads the `can_routes` section on a cached boot, by design. The contributor would run a second full `RouteCollector` scan and store unused data, which goes against "no pseudo-functionality". **Fix:** drop task 005 and the `discovery` declaration. 006 instead tests that `discovery:cache`'s live boot fails.

4. **Token guard now needs `TokenRepositoryInterface` at boot (006).** `authentication-token/module.php` says "booting never needs a TokenRepositoryInterface". Building the `token` guard at boot resolves `TokenGuardFactory` and therefore the repository. That is correct "fail the boot" behaviour, but the token-driver test must bind a repository, the existing `TokenGuardWiringTest` must still pass, and the module.php comment must be updated.

## Minor (Nice to address)

- 002: the suggestion that names "a session driver" is misleading when the guard driver is `token` or custom. Consider phrasing the suggestion around the failing guard's driver.
- An observer on `ApplicationBooted` is still resolved on cached boots (one tiny object). Keep its constructor to `CachedDiscovery` and `ContainerInterface` so it resolves nothing else before the `isCached()` return.
- 007: also update `roadrunner-state-leaks.md` if it describes when the Gate is built.

## Questions for the Team

- Every live CLI boot (`discovery:clear`, migrations, dev commands) now fails on an auth misconfiguration while `#[Can]` routes exist. Is that acceptable for `discovery:clear` in particular? It is the recovery command.
- Is a core `ApplicationBooted` event acceptable for this issue, or would you rather have a module.php `booted` callback key? The event is the smaller, discoverable option.
