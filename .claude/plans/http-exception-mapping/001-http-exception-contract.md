# Task 001: HttpExceptionInterface, HttpException and HttpStatus

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add the core contract and the routing-side concrete exception with named constructors for common statuses.

## Context
- Related files: packages/core/src/Exceptions/HttpExceptionInterface.php, packages/routing/src/Exceptions/HttpException.php, packages/routing/src/Http/HttpStatus.php

## Requirements (Test Descriptions)
- [x] `it defines HttpExceptionInterface extending Throwable with status, headers and response data`
- [x] `it creates an HttpException with status, message and headers`
- [x] `it defaults the message to the status reason phrase`
- [x] `it rejects status codes outside the 4xx and 5xx range`
- [x] `it creates common statuses through named constructors`
- [x] `it sets the Allow header for methodNotAllowed`
- [x] `it sets the Retry-After header for tooManyRequests when given`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

