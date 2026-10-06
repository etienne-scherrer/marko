# Plan: Boot-Time Validation for #[Can] Routes

## Created
2026-10-05

## Status
completed

## Objective
Fail a live boot, and `marko discovery:cache`, when any route uses `#[Can]` but the authorization guard or the Gate cannot be built, without adding any work to a boot served from the discovery cache.

## Related Issues
Closes #263

## Discovery Notes
- `AuthorizationMiddleware` builds the Gate and guard lazily on the first `#[Can]` route (#249). Misconfiguration therefore surfaces on the first `#[Can]` request.
- `RoutingBootstrapper::boot()` registers `RouteCollection` in the container before module boot callbacks run, on both live and cached boots.
- `CachedDiscovery::isCached()` tells a boot callback whether this boot came from the discovery cache. `discovery:cache` and `discovery:clear` always boot live.
- Cached route records hold no attributes, so finding `#[Can]` routes needs reflection; that must only happen on live boots and at cache-compile time.
- `marko/authentication-token` registers the `token` guard driver in its `boot` callback and is sequenced before `marko/authorization`, so its boot callback runs first.
- `RouteCollector` (routing) is autowirable at cache-compile time: `PreferenceRegistry` and `ClassFileParser` are container instances.
- Custom guard drivers are registered from module `boot` callbacks, usually in app modules that are not sequenced before `marko/authorization`. The check must therefore run after all boot callbacks, which needs a core `ApplicationBooted` event (task 008).
- `discovery:cache` and `discovery:clear` always boot live (`CliKernel::LIVE_DISCOVERY_COMMANDS`), so the post-boot check already fails `discovery:cache`. A cache contributor would be redundant (task 005 removed).
- `GateInterface` is a singleton that captures the guard when it is built. `TestClient::actingAs()` swaps the guard via `AuthManager::useGuard()` after boot, so the check must never resolve `GateInterface`.

## Scope

### In Scope
- `CanAttributeReader`: one place that reads `#[Can]` (method first, then class); the middleware uses it.
- `CanRouteFinder`: lists `controller::action` keys of routes with `#[Can]` that keep `AuthorizationMiddleware`.
- `CanConfigurationValidator`: finds the routes and builds the authorization guard and the Gate's collaborators (never the `GateInterface` singleton) once, throwing `AuthorizationConfigurationException` on failure.
- Core `ApplicationBooted` event dispatched after all module boot callbacks.
- Authorization observer on `ApplicationBooted` that validates live boots only.
- Docs page and README updates.

### Out of Scope
- A `marko authorization:check` command (alternative rejected in the issue).
- `WWW-Authenticate` on `#[Can]` 401s (#268).
- Changing the lazy factories in `AuthorizationMiddleware`.

## Success Criteria
- [x] Live boot with a `#[Can]` route and no usable guard fails, naming the guard and a `#[Can]` route
- [x] Live boot with no `#[Can]` routes and no auth configuration succeeds
- [x] A `#[Can]` route excluding `AuthorizationMiddleware` doesn't trigger the check
- [x] `discovery:cache` (through its live boot) fails on the same misconfiguration; a cached boot builds nothing
- [x] Works with the `token` default guard from `marko/authentication-token` and with custom drivers registered in app boot callbacks
- [x] `actingAs()` still authorizes `#[Can]` routes after a live boot
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Extract CanAttributeReader and use it in the middleware | - | completed |
| 002 | AuthorizationConfigurationException | - | completed |
| 003 | CanRouteFinder | 001 | completed |
| 004 | CanConfigurationValidator | 002, 003 | completed |
| 005 | ~~CanRouteCacheContributor~~ (removed: redundant, discovery:cache boots live) | - | removed |
| 006 | ApplicationBooted observer wiring (incl. token guard, custom drivers, actingAs regression) | 004, 008 | completed |
| 007 | Docs page and README | 006 | completed |
| 008 | Core ApplicationBooted event after all module boot callbacks | - | completed |

## Architecture Notes
- The `ApplicationBooted` observer checks `CachedDiscovery::isCached()` first and returns before resolving anything else, so a cached boot does no reflection and builds no Gate or guard. Its constructor takes only `CachedDiscovery` and `ContainerInterface`.
- Logic lives in classes; `module.php` only wires them.
- `CanConfigurationValidator` resolves services through the container on purpose: its job is to prove the container can build them, and to wrap whatever the container throws.

## Risks & Mitigations
- Other packages' tests boot live with `#[Can]` routes and no auth config: run the full suite and fix fixtures.
- Live CLI boots (development, `discovery:cache`, `discovery:clear`) also fail on misconfiguration: documented; this is the "fail the boot" behaviour the issue asks for.
