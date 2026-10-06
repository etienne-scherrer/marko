# Task 006: UrlGenerator, Routing Config and Module Bindings

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Add `UrlGeneratorInterface` and `UrlGenerator::route(name, parameters, absolute)`. Placeholders are filled with `rawurlencode`d values (slashes kept in catch-alls), unused parameters become a query string, and constraint violations and missing parameters throw `RouteException`. Absolute URLs use `routing.url` (from `APP_URL`) and throw when it is empty. Add `config/routing.php`, `RoutingConfig`, a `module.php` singleton, and require `marko/config`.

## Context
- Related files: packages/routing/src/UrlGenerator.php, UrlGeneratorInterface.php, RoutingConfig.php, Exceptions/UrlGenerationException.php (new), config/routing.php, module.php, composer.json
- Patterns to follow: packages/session/src/Config/SessionConfig.php

## Contract (tasks 008/009 build against this)
```php
namespace Marko\Routing;

interface UrlGeneratorInterface
{
    /**
     * @param array<string, string|int|float|bool> $parameters
     * @throws Exceptions\UrlGenerationException
     */
    public function route(string $name, array $parameters = [], bool $absolute = false): string;
}
```
Build on the existing pieces from 001/003: `RouteCollection::named(string $name): ?RouteDefinition`, `RouteDefinition::$parameters` (path order), `$catchAll` (?string), and `satisfiesConstraint(string $parameter, string $value): bool`. Do not re-implement constraint checks.

## Wiring notes
- Put all new factories in `Exceptions/UrlGenerationException extends RouteException`. Do NOT edit `RouteException.php`, because task 005 edits it in parallel.
- `config/routing.php` returns `['url' => $_ENV['APP_URL'] ?? '']`. `ConfigDiscovery` loads the module's `config/` directory automatically once `marko/routing` requires `marko/config`.
- `module.php`: `'singletons' => [UrlGeneratorInterface::class => UrlGenerator::class]` (keyed form; nothing else). `RouteCollection` is registered as an instance during `Application::initialize()` (before boot callbacks and any user resolution), so autowiring it into `UrlGenerator` is safe.

## Requirements (Test Descriptions)
- [x] `it generates a relative URL for a named route`
- [x] `it encodes parameter values`
- [x] `it keeps slashes in catch-all values` (each segment rawurlencoded separately; no empty segments from a leading or trailing slash)
- [x] `it appends unused parameters as a query string` (RFC 3986 encoding)
- [x] `it casts int, float and bool parameter values to strings`
- [x] `it generates an absolute URL from the configured base URL`
- [x] `it joins a base URL with a trailing slash or sub-path without a double slash`
- [x] `it throws when an absolute URL is requested without a base URL`
- [x] `it throws when a required parameter is missing`
- [x] `it throws when a required parameter is an empty string`
- [x] `it throws when a path parameter value is not a scalar`
- [x] `it throws when a parameter violates its constraint`
- [x] `it throws when the route name is unknown`
- [x] `it generates URLs that the route matcher resolves back to the same parameters` (round trip through `RouteMatcher::match()`, including encoded characters and a catch-all)
- [x] `it binds UrlGeneratorInterface as a singleton in module.php`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
