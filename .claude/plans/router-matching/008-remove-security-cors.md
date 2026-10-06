# Task 008: Remove the duplicate security CorsMiddleware

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Delete `Marko\Security\Middleware\CorsMiddleware`, its tests, the `security.cors` config block and the `cors*()` getters on `SecurityConfig`. `marko/cors` is the single CORS implementation.

## Context
- Related files: packages/security/src/Middleware/CorsMiddleware.php, src/Config/SecurityConfig.php, config/security.php, tests/

## Requirements (Test Descriptions)
- [x] `it does not ship a CORS middleware in marko/security`
- [x] `it has no cors keys in the security config`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
