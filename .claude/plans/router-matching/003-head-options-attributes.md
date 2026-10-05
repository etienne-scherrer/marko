# Task 003: Head and Options route attributes

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `#[Head]` and `#[Options]` attributes extending `Route` so explicit HEAD/OPTIONS routes can be declared and discovered.

## Context
- Related files: packages/routing/src/Attributes/, tests/Attributes/RouteAttributesTest.php, RouteDiscovery
- packages/codeindexer/src/Attributes/AttributeParser.php `routes()` hardcodes the attribute-to-method map (Get/Post/Put/Patch/Delete); add `Head` => 'HEAD' and `Options` => 'OPTIONS' plus a codeindexer test, or the new routes are invisible to the code index.
- Follow the existing attribute shape exactly (`#[Attribute(Attribute::TARGET_METHOD | ...)]`, readonly, extends `Route`, `getMethod()`), e.g. copy `Delete.php`.

## Requirements (Test Descriptions)
- [x] `it returns HEAD from the Head attribute`
- [x] `it returns OPTIONS from the Options attribute`
- [x] `it discovers Head and Options routes on controller methods`
- [x] `it indexes Head and Options route attributes` (codeindexer AttributeParser)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
