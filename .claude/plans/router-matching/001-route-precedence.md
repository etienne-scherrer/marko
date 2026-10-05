# Task 001: Route specificity and precedence ordering in RouteCollection

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Give `RouteDefinition` derived specificity data and make `RouteCollection::byMethod()` return routes in effective match order, independent of registration order.

## Context
- Related files: packages/routing/src/RouteDefinition.php, packages/routing/src/RouteCollection.php, packages/routing/tests/RouteCollectionTest.php
- Order: static first; dynamic by static-segment count desc, then static-prefix length desc, then registration order. Sort once per method, cache, invalidate on add().

## Requirements (Test Descriptions)
- [x] `it marks a route without parameters as static`
- [x] `it counts static segments and static prefix length for dynamic routes`
- [x] `it orders static routes before dynamic routes regardless of registration order`
- [x] `it orders dynamic routes by descending static segment count`
- [x] `it orders dynamic routes with equal static segments by descending static prefix length`
- [x] `it keeps registration order for routes of equal specificity`
- [x] `it returns a static route by exact path lookup`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
