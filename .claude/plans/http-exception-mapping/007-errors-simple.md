# Task 007: errors-simple JSON negotiation and last-resort status

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
SimpleErrorHandler renders JSON when the client asks for it and uses an HttpExceptionInterface status instead of 500.

## Context
- Related files: packages/errors-simple/src/SimpleErrorHandler.php, packages/errors-simple/src/Environment.php, packages/errors-simple/src/Formatters/JsonFormatter.php

## Requirements (Test Descriptions)
- [x] `it detects a JSON-accepting request from the server Accept header`
- [x] `it renders a generic JSON body in production`
- [x] `it renders message, class and trimmed trace as JSON in development`
- [x] `it uses the status of an HttpExceptionInterface that reaches the handler`
- [x] `it sends the JSON content type header`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

