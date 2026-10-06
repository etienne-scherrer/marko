# Task 004: RoutePrefix Attribute and Preference Carry-through

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Add class-level `#[RoutePrefix(prefix, namePrefix)]`. `RouteDiscovery` joins prefix and path (normalizing slashes) and prepends `namePrefix` to named routes. The prefix is resolved from the method's declaring class, walking up to the nearest ancestor with a prefix, so it survives a controller `#[Preference]`.

## Context
- Related files: packages/routing/src/Attributes/RoutePrefix.php (new), RouteDiscovery.php, PreferenceRouteResolver.php, tests/Fixtures

## Requirements (Test Descriptions)
- [x] `it prepends the class prefix to every route path`
- [x] `it normalizes slashes when joining prefix and path`
- [x] `it prepends the name prefix to named routes`
- [x] `it leaves unnamed routes unnamed when a name prefix is set`
- [x] `it keeps the parent prefix on routes inherited through a Preference`
- [x] `it applies the parent prefix to a Preference override without its own prefix`
- [x] `it throws when a prefix does not start with a slash`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
