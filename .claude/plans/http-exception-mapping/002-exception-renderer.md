# Task 002: ExceptionRenderer

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Plain, Preference-able class that turns an HttpExceptionInterface into a Response, negotiating JSON vs minimal HTML.

## Context
- Related files: packages/routing/src/Http/ExceptionRenderer.php

## Requirements (Test Descriptions)
- [x] `it renders JSON when Accept contains application/json`
- [x] `it renders JSON when Accept contains a +json media type`
- [x] `it renders JSON when Content-Type is JSON and Accept is absent`
- [x] `it renders a minimal escaped HTML page otherwise`
- [x] `it applies the exception status and headers`
- [x] `it falls back to the reason phrase when response data has no message`
- [x] `it never reads the exception message directly`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

