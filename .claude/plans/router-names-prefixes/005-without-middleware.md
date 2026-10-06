# Task 005: WithoutMiddleware Attribute, Router Exclusion and Boot Validation

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Add `#[WithoutMiddleware]` (class or method, class-string or array). Store excluded middleware on `RouteDefinition`. `Router` drops excluded classes from the global + route stack. `RoutingBootstrapper::boot()` throws when a route excludes a middleware that is neither global nor on the route.

## Context
- Related files: packages/routing/src/Attributes/WithoutMiddleware.php (new), RouteDefinition.php, RouteDiscovery.php, Router.php, RoutingBootstrapper.php, Exceptions/RouteException.php

## Contract
- `#[WithoutMiddleware(string|array $middleware)]`, `Attribute::TARGET_CLASS | Attribute::TARGET_METHOD`, not repeatable; normalize to `public array $middleware` (list of class-strings).
- `RouteDefinition` gains `public array $withoutMiddleware = []` as the LAST constructor argument (after `?string $name`), so existing positional constructions keep working. `withController()` (clone-with) carries it automatically; verify.
- Effective exclusions = class-level ∪ method-level, deduplicated, order preserved.

## Class-level attribute resolution (Preference safety)
`discoverFromClass()` currently reads class-level `#[Middleware]` only from the discovered class. A `#[Preference]` child is discovered directly and its inherited methods come through that discovery, so the parent's class-level attributes are lost. That would make boot validation throw for a valid parent (`#[Middleware(X)]` on the class, `#[WithoutMiddleware(X)]` on a method), and would silently drop a parent's class-level exclusion. Collect class-level `#[Middleware]` AND `#[WithoutMiddleware]` from the discovered class plus all ancestors (ancestor first, deduplicated). This also fixes a parent's class-level auth middleware being dropped by a Preference.

## Requirements (Test Descriptions)
- [x] `it collects excluded middleware from class and method attributes`
- [x] `it skips an excluded global middleware for the route`
- [x] `it still runs the excluded middleware on other routes`
- [x] `it skips an excluded route middleware`
- [x] `it still runs every global middleware for unmatched requests`
- [x] `it throws at boot when a route excludes middleware that is not in its stack` (message names the route method/path, controller::action and the excluded class; suggestion lists the effective stack)
- [x] `it keeps class-level excluded middleware on routes inherited through a Preference`
- [x] `it keeps the parent's class-level middleware on routes inherited through a Preference`
- [x] `it does not throw at boot when a Preference inherits a route that excludes its parent's class middleware`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
