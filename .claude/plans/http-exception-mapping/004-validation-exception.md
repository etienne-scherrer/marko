# Task 004: ValidationException maps to 422

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
ValidationException implements HttpExceptionInterface with the errors in its response data.

## Context
- Related files: packages/validation/src/Exceptions/ValidationException.php, packages/validation/composer.json

## Requirements (Test Descriptions)
- [x] `it implements HttpExceptionInterface with status 422`
- [x] `it exposes message and errors in response data`
- [x] `it renders 422 with an errors object matching ValidationErrors::all`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

