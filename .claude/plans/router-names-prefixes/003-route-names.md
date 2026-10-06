# Task 003: Route Names and Duplicate-name Detection

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `?string $name` to the `Route` attribute base and to `RouteDefinition`, carry it through discovery and Preference resolution (via `withController()`), and index names in `RouteCollection` with a loud duplicate-name error naming both locations.

## Context
- Related files: packages/routing/src/Attributes/Route.php, RouteDefinition.php, RouteCollection.php, RouteDiscovery.php, PreferenceRouteResolver.php, Exceptions/RouteConflictException.php

## Requirements (Test Descriptions)
- [x] `it accepts a name on every route attribute`
- [x] `it stores the route name from the attribute on the definition`
- [x] `it finds a route by name`
- [x] `it throws a duplicate-name conflict naming both controller actions`
- [x] `it keeps the route name when a Preference inherits the route`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
