# Task 003: CanRouteFinder

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Finds the controller::action keys of every route that carries #[Can] and keeps AuthorizationMiddleware in its stack.

## Context
- Related files: packages/routing/src/RouteCollection.php, RouteDefinition.php
- Patterns to follow: existing authorization package classes; DiscoveryCacheContributorInterface implementations

## Requirements (Test Descriptions)
- [x] `it returns the controller::action keys of routes with a method-level Can`
- [x] `it includes routes protected by a class-level Can`
- [x] `it skips routes without Can`
- [x] `it skips Can routes that exclude AuthorizationMiddleware`
- [x] `it lists an action once when several routes point to it`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Keys are deduplicated and kept in route order.
