# Devil's Advocate Review: router-names-prefixes

Review note: the worktree already holds work-in-progress for tasks 001-004 (`RouteDefinition` exposes `constraints`/`catchAll`/`satisfiesConstraint()`/`name`, `RouteCollection::named()` and `duplicateName()` exist, `RouteDiscovery::resolvePrefix()`/`joinPrefix()` and `RouteException::invalidPrefix()` exist, and `PreferenceRouteResolver` uses `withController()`). These findings focus on the remaining tasks (005-011) and on the contracts that downstream workers need from the finished ones.

## Critical (Must fix before building)

1. **Task 005: false-positive boot failure for Preference children (class-level middleware is dropped).** `RouteDiscovery::discoverFromClass()` reads class-level `#[Middleware]` only from the *discovered* class (`$this->getClassMiddleware($reflection)`). A `#[Preference]` child is discovered directly, and because `getMethods()` includes inherited methods, every parent route comes back through the child's discovery (`childHasRoute` is true in `PreferenceRouteResolver`, so the parent's definition is thrown away). The parent's class-level `#[Middleware]` is lost. That is already a pre-existing security bug: a parent-level `#[Middleware(AuthMiddleware::class)]` silently disappears when an app prefers the controller. Task 005 makes it worse. If a parent has class `#[Middleware(X)]` and a method `#[WithoutMiddleware(X)]`, then under a Preference X is missing from the route stack while the exclusion is still there, so the new boot validation throws even though the config is valid. If 005 instead reads class-level `#[WithoutMiddleware]` the same way as `#[Middleware]` (discovered class only), a parent's class-level `#[WithoutMiddleware(SessionMiddleware::class)]` silently disappears under a Preference and the stateless API starts setting cookies again. **Fix applied to 005:** gather class-level `#[Middleware]` and `#[WithoutMiddleware]` from the discovered class *and* its ancestors (ancestor first, deduplicated), and add tests for both under a Preference.

## Important (Should fix before building)

2. **Task 006: concurrent edits to `RouteException.php`.** Tasks 006 and 005 can run in parallel (006 depends only on 001, 003). Both would add factories to `Exceptions/RouteException.php` (005 for the boot validation, 006 for missing, constraint-violating or unknown routes and a missing base URL). **Fix applied:** 006 puts its factories in a new `Exceptions/UrlGenerationException extends RouteException`.

3. **Task 006: interface contract for 008/009 is unspecified.** The view workers need the exact signature. **Fix applied:** `route(string $name, array<string, string|int|float|bool> $parameters = [], bool $absolute = false): string` on `Marko\Routing\UrlGeneratorInterface`, plus the 001/003 contracts 006 must build on: `RouteCollection::named()`, `RouteDefinition::$parameters`, `$catchAll` and `satisfiesConstraint()`.

4. **Task 006: missing edge cases that violate "loud errors".**
   - An empty-string value for a required parameter would produce `/posts/`, which never matches (the matcher normalizes away the trailing slash). It should throw.
   - A non-scalar value (array, object, null) for a path placeholder should throw. Booleans and numbers should be cast to strings before the constraint check.
   - `APP_URL` with a trailing slash (`https://x.test/`) or a sub-path (`https://x.test/app`) must join without a double slash.
   - A catch-all value must be encoded per segment (`implode('/', array_map('rawurlencode', explode('/', $v)))`), and a leading or trailing slash must not create empty segments.
   - Round-trip guarantee: a generated URL, run through `RouteMatcher::match()`, must return the original parameters (the matcher `rawurldecode`s once).

   **Fix applied:** added these as requirements.

5. **Task 006: `marko/routing` gaining `module.php`, `config/routing.php` and a `marko/config` dependency.** `ConfigDiscovery` loads every module's `config/` directory, so the default is picked up automatically. `RouteCollection` is registered as an instance during `Application::initialize()` before boot callbacks run, so a lazily resolved singleton is safe. The task should state this explicitly, and should use `$_ENV['APP_URL'] ?? ''` to match the #160 convention. **Fix applied.**

6. **Tasks 008/009: blast radius of the factory constructor change.** `LatteEngineFactory` is constructed directly in `view-latte/tests/LatteEngineFactoryTest.php`, `tests/Feature/IntegrationTest.php` and `tests/Unit/Extensions/SlotExtensionTest.php`. `TwigEngineFactory` is constructed in `view-twig/tests/TwigEngineFactoryTest.php`. Container-resolved engines now also need `UrlGeneratorInterface` → `UrlGenerator` → `RoutingConfig` → `ConfigRepositoryInterface`, so any lightweight test container that resolves `Engine`/`Environment` without config will fail. Also, `view-latte` and `view-twig` reach `marko/routing` only transitively through `marko/view`. "Explicit over implicit" calls for a direct require now that they import `Marko\Routing\UrlGeneratorInterface`. **Fix applied:** listed the files, added the direct composer requirements, and required a full `composer test` run.

7. **Tasks 008/009: error propagation.** A `RouteException` from the generator must not be swallowed or wrapped into an empty string by the template engine. Twig wraps exceptions thrown during render in `Twig\Error\RuntimeError` (the original is kept as `previous`). The tests should assert that an unknown route name fails the render loudly. **Fix applied** (requirement added).

8. **Task 005: attribute shape and `RouteDefinition` argument order are unspecified.** `RouteDefinition` now has the trailing `?string $name = null`. 005 must add `array $withoutMiddleware = []` *after* `name` so that the existing positional constructions (230+ call sites in tests) keep working, and `withController()` must carry it. **Fix applied:** specified `#[WithoutMiddleware(string|array $middleware)]`, `TARGET_CLASS | TARGET_METHOD`, not repeatable, class and method merged and deduplicated.

9. **Task 010: the integration test runs in the `integration-services` group (Postgres + Redis).** `composer test` alone won't exercise it. The fixture route must be added to `IntegrationController`, and boot validation will pass only because the fixture uses `session-database` (which registers `SessionMiddleware` globally). **Fix applied:** notes added.

## Minor (Nice to address)

- Task 006: an unknown-name error could suggest close matches (`levenshtein` over `RouteCollection` names), the way other Marko exceptions do.
- Task 006: a non-catch-all value containing `/` is encoded as `%2F`. That round-trips in Marko, but Apache (`AllowEncodedSlashes Off`) and some proxies reject it. Consider throwing or documenting it.
- Task 006: `packages/skeleton/.env.example` should gain `APP_URL=` so absolute URLs work out of the box (doc-updater or 011 can cover this).
- Task 009 puts `RouteExtension` in `src/` while 008 uses `src/Extensions/`. That is consistent with each package's existing layout (Latte already has `Extensions/SlotExtension`), so it's fine either way.
- Task 007: a `--name` filter on `route:list` would be a natural addition but isn't required by #172.
- Task 010/011: the stateless-API recipe should note that `AuthorizationMiddleware` (global, from `marko/authorization`) injects `GuardInterface`. Stateless routes protected by `#[Can]` need a token guard, otherwise `SessionGuard` throws `SessionNotStartedException` (loud, but surprising).
- A constraint such as `{path:.+}` behaves like a catch-all for matching, but `catchAll` stays null, so it sorts as a constrained route and the generator encodes its slashes.

## Questions for the Team

1. **Catch-all and the empty remainder:** `/docs/{path*}` compiles to `.+`, so `/docs` and `/docs/` do not match it. Is that intended, or should a catch-all also match an empty remainder?
2. **Catch-all precedence:** the implemented sort puts *every* catch-all after *every* other dynamic route regardless of static segment count, so `/{a}/{b}` beats `/admin/{path*}` for `/admin/x`. Is that the desired rule, or should catch-alls only lose ties at equal static segment count?
3. **Preference with its own `#[RoutePrefix]`:** a prefix comes from the method's declaring class, so a Preference that adds `#[RoutePrefix('/v2')]` changes only the methods it overrides; inherited routes keep the parent prefix. Is that intended?
4. **`#[WithoutMiddleware]` in reusable modules:** boot validation throws when the excluded middleware is not in the stack. A module that ships `#[WithoutMiddleware(SessionMiddleware::class)]` will break boot for apps without a session driver. Should a *global* middleware that isn't registered be allowed (no-op) while unknown *route* middleware still throws?
5. **Class-level middleware inheritance (finding 1):** the fix changes class-level `#[Middleware]` from "discovered class only" to "discovered class + ancestors". Confirm this is acceptable (it also fixes Preferences silently dropping a parent's auth middleware).
