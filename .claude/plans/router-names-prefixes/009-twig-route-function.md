# Task 009: route() in Twig Templates

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Add a Twig extension providing a `route()` function backed by the injected `UrlGeneratorInterface`, registered by `TwigEngineFactory`.

## Context
- Related files: packages/view-twig/src/RouteExtension.php, TwigEngineFactory.php, composer.json, tests
- Contract: `Marko\Routing\UrlGeneratorInterface::route(string $name, array $parameters = [], bool $absolute = false): string` (see task 006)

## Blast radius (update all of these)
- `TwigEngineFactory` gains a `UrlGeneratorInterface` constructor argument. Update the direct constructions in `packages/view-twig/tests/TwigEngineFactoryTest.php` with a stub generator.
- Add `"marko/routing": "self.version"` to `packages/view-twig/composer.json` `require`.
- Run the full `composer test` (container-resolved `Twig\Environment` now also needs `UrlGeneratorInterface` resolvable).
- Do NOT mark the function `is_safe`. Keep autoescaping so `&` in query strings is escaped in HTML.

## Requirements (Test Descriptions)
- [x] `it renders a route URL with the route function`
- [x] `it passes parameters and the absolute flag to the URL generator`
- [x] `it lets a URL generation exception fail the render` (Twig wraps it in `RuntimeError`; assert the previous exception is the routing exception)
- [x] `it registers the route extension on the environment`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
