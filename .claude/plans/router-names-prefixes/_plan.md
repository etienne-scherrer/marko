# Plan: Router Names, Prefixes, Catch-all and WithoutMiddleware

## Created
2026-10-05

## Status
completed

## Objective
Add named routes with a `UrlGenerator`, class-level `#[RoutePrefix]`, catch-all (`{path*}`) and regex-constrained (`{id:\d+}`) parameters, and per-route global middleware exclusion (`#[WithoutMiddleware]`) to `marko/routing`, with `route()` in Latte and Twig templates.

## Related Issues
Closes #172

## Discovery Notes
- `RouteDefinition` derives regex, parameters and precedence keys from the path in its constructor; every `{param}` compiles to `[^/]+`.
- `RouteCollection` (from #171) sorts per method: static first, then static segment count, then static prefix length, then registration order.
- `RouteDiscovery::discoverFromClass()` walks `getMethods()` (including inherited methods), so a `#[Preference]` child re-discovers the parent's routes under its own class. Class-level attributes are not inherited by PHP, so a prefix must be resolved from the method's declaring class hierarchy to survive a Preference.
- `PreferenceRouteResolver` rebuilds `RouteDefinition`s by hand and would drop any new field; replace with a `withController()` clone.
- `Router` runs `[...global, ...route]` middleware. `SessionMiddleware` is global via `session-file`/`session-database` module.php. `Session` already throws `SessionNotStartedException` when used unstarted, and `SessionGuard` throws when the session is not started, so skipping the session is loud by default.
- `marko/routing` has no module.php, no config dir and no `marko/config` dependency. Config files read env as `$_ENV['X'] ?? default` (#160 mirrors real env vars into `$_ENV`).
- `marko/view` already requires `marko/routing`, so view drivers can inject `UrlGeneratorInterface`.
- Route metadata stays cacheable for #173: every `RouteDefinition` constructor argument is a scalar or a list of strings (method, prefix-resolved path, controller, action, middleware, name, withoutMiddleware). Constraints and catch-alls live in the path, and derived fields are recomputed by the constructor.

## Scope

### In Scope
- `{name:regex}` and `{name*}` parsing, validation at boot, precedence integration
- `name` on route attributes, `RouteCollection` name index, duplicate-name error
- `#[RoutePrefix(prefix, namePrefix)]`, Preference carry-through
- `#[WithoutMiddleware]` (class/method), `Router` exclusion, boot validation
- `UrlGeneratorInterface`/`UrlGenerator`, `routing.url` config (`APP_URL`), module.php singleton
- `route:list` NAME column
- `route()` function in Latte and Twig
- Session stateless API tests (unit + integration fixture) and docs

### Out of Scope
- `Response::redirectToRoute()` (follow-up)
- Route/discovery cache (#173)
- Global `route()`/`url()` PHP helpers, route model binding

## Success Criteria
- [ ] All exit criteria in #172 have tests
- [ ] Every misconfiguration fails at boot with context and suggestion
- [ ] Docs: routing.md, view-latte.md, view-twig.md, session.md + READMEs
- [ ] All tests passing
- [ ] Code follows project standards (`composer ci` green)

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Constrained and catch-all path parameters | - | completed |
| 002 | Precedence for constrained and catch-all routes | 001 | completed |
| 003 | Route names and duplicate-name detection | 001 | completed |
| 004 | RoutePrefix attribute and Preference carry-through | 003 | completed |
| 005 | WithoutMiddleware attribute, Router exclusion, boot validation | 003, 004 | completed |
| 006 | UrlGenerator, routing config and module bindings | 001, 003 | completed |
| 007 | route:list name column | 003, 004 | completed |
| 008 | route() in Latte templates | 006 | completed |
| 009 | route() in Twig templates | 006 | completed |
| 010 | Stateless session routes (session + integration tests) | 005 | completed |
| 011 | Documentation and READMEs | 001-010 | completed |

## Architecture Notes
- The path parser lives in `RouteDefinition` and tracks brace depth so `{year:\d{4}}` works. Parameter names must be identifiers. A catch-all must be the whole final segment. Regexes are validated with `preg_match` and rejected when they contain capturing groups (detected through the `PREG_UNMATCHED_AS_NULL` group count).
- `RouteDefinition::withController()` uses PHP 8.5 `clone($this, [...])` so new fields survive Preference resolution.
- `Router` computes `array_values(array_diff([...global, ...route], route->withoutMiddleware))`; `RoutingBootstrapper::boot()` validates exclusions against global + route middleware.
- `UrlGenerator` reads `RouteCollection` (registered by the bootstrapper) and `RoutingConfig` (`routing.url`).
- Shared contracts (from the devil's advocate review): `RouteDefinition` exposes `parameters`, `constraints`, `catchAll`, `name`, `satisfiesConstraint()`, `withController()`. `withoutMiddleware` (005) is appended as the last constructor argument. `RouteCollection::named(string): ?RouteDefinition`. `UrlGeneratorInterface::route(string $name, array $parameters = [], bool $absolute = false): string`.
- Class-level `#[Middleware]` and `#[WithoutMiddleware]` are gathered from the discovered class plus its ancestors (005), so a Preference child keeps its parent's class-level stack and exclusions, and boot validation has no false positives.
- 006 puts its exceptions in `UrlGenerationException extends RouteException` so it doesn't edit `RouteException.php` at the same time as 005.

## Risks & Mitigations
- Changing precedence could reorder existing routes: constraints and catch-alls only add keys, so unconstrained, non-catch-all routes keep the #171 order.
- Twig/Latte factory constructor change breaks existing tests: update them with a stub `UrlGeneratorInterface` (files listed in 008/009). view-latte and view-twig gain a direct `marko/routing` requirement.
