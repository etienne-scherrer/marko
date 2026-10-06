# Task 002: CORS opts in to unmatched requests

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Mark `CorsMiddleware` with `#[RunsOnUnmatched]` so preflights (which reach the router as unmatched OPTIONS requests) and 404/405 responses keep their CORS headers.

## Context
- Related files: packages/cors/src/Middleware/CorsMiddleware.php, packages/cors/tests/CorsGlobalTest.php

## Requirements (Test Descriptions)
- [x] `it declares RunsOnUnmatched so preflights reach it when no route matches`
- [x] `it answers a cross-origin preflight to a POST-only route with 204 and CORS headers`
- [x] `it answers a preflight to an unknown covered path with 204 and CORS headers`
- [x] `it adds CORS headers to a 404 for a non-preflight request to an unknown path`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
