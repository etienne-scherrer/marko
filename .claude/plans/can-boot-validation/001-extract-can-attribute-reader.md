# Task 001: Extract CanAttributeReader

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Move the `#[Can]` lookup (method attribute, else class attribute) out of `AuthorizationMiddleware` into a reusable class so the middleware and the boot check read attributes the same way.

## Context
- Related files: packages/authorization/src/Middleware/AuthorizationMiddleware.php
- Patterns to follow: existing authorization package classes; DiscoveryCacheContributorInterface implementations

## Requirements (Test Descriptions)
- [x] `it returns the method-level Can attribute`
- [x] `it falls back to the class-level Can attribute`
- [x] `it prefers the method-level Can over the class-level one`
- [x] `it returns null when neither the method nor the class has Can`
- [x] `AuthorizationMiddleware existing tests keep passing`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
CanAttributeReader lives in src/Routing; AuthorizationMiddleware takes it as an optional constructor argument (default new CanAttributeReader()), so module.php wiring is unchanged.
