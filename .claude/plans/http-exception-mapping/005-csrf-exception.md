# Task 005: CsrfTokenMismatchException maps to 419

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
CSRF rejection becomes a 419 response that outer middleware (including marko/cors) still decorates.

## Context
- Related files: packages/security/src/Exceptions/CsrfTokenMismatchException.php

## Requirements (Test Descriptions)
- [x] `it implements HttpExceptionInterface with status 419`
- [x] `it returns 419 when CsrfMiddleware rejects a request through the pipeline`
- [x] `it lets an outer header middleware and CorsMiddleware decorate the 419 response`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

