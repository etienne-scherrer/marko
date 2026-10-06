# Task 008: route() in Latte Templates

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Add a Latte extension providing a `route()` function backed by the injected `UrlGeneratorInterface`, registered by `LatteEngineFactory`.

## Context
- Related files: packages/view-latte/src/Extensions/RouteExtension.php, LatteEngineFactory.php, composer.json, tests
- Contract: `Marko\Routing\UrlGeneratorInterface::route(string $name, array $parameters = [], bool $absolute = false): string` (see task 006)

## Blast radius (update all of these)
- `LatteEngineFactory` gains a `UrlGeneratorInterface` constructor argument. Update the direct constructions in `packages/view-latte/tests/LatteEngineFactoryTest.php`, `tests/Feature/IntegrationTest.php` and `tests/Unit/Extensions/SlotExtensionTest.php` with a stub generator.
- Add `"marko/routing": "self.version"` to `packages/view-latte/composer.json` `require` (it now imports a routing interface directly; don't rely on the transitive path through `marko/view`).
- Run the full `composer test`. Any test that resolves `Latte\Engine` through a real container now also needs `UrlGeneratorInterface` resolvable (which pulls in `RoutingConfig` → `ConfigRepositoryInterface`).

## Requirements (Test Descriptions)
- [x] `it renders a route URL with the route function`
- [x] `it passes parameters and the absolute flag to the URL generator`
- [x] `it escapes the generated URL in an attribute context` (e.g. `&` in the query string becomes `&amp;`)
- [x] `it lets a URL generation exception fail the render` (unknown route name is not swallowed)
- [x] `it registers the route extension on the engine`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
