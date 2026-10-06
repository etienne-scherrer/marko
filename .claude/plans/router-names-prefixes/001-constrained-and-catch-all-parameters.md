# Task 001: Constrained and Catch-all Path Parameters

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Teach `RouteDefinition` to parse `{name:regex}` and `{name*}` placeholders, compile them into the route regex and expose constraint metadata. Invalid definitions throw `RouteException` when the definition is built (at boot).

## Context
- Related files: packages/routing/src/RouteDefinition.php, packages/routing/src/Exceptions/RouteException.php, packages/routing/src/RouteMatcher.php
- Patterns to follow: existing `RouteException` factories (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it compiles a constrained parameter into its regex`
- [x] `it matches a catch-all parameter across slashes`
- [x] `it rejects a value that does not satisfy the parameter constraint`
- [x] `it supports braces inside a constraint regex`
- [x] `it throws when a catch-all parameter is not the final segment`
- [x] `it throws when a constraint regex is invalid`
- [x] `it throws when a constraint regex contains a capturing group`
- [x] `it throws when a parameter name is not a valid identifier`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
