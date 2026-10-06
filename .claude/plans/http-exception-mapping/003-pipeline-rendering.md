# Task 003: Render HTTP exceptions inside MiddlewarePipeline

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Catch HttpExceptionInterface around every middleware and the terminal handler so outer middleware decorates the response; remove the hand-written 400 in Router.

## Context
- Related files: packages/routing/src/Middleware/MiddlewarePipeline.php, packages/routing/src/Router.php, packages/routing/src/Exceptions/InvalidRouteParameterException.php

## Requirements (Test Descriptions)
- [x] `it returns a 404 response when a controller throws HttpException::notFound`
- [x] `it returns a JSON body when the request accepts JSON`
- [x] `it lets outer middleware decorate a response rendered from an inner exception`
- [x] `it propagates non-HTTP exceptions unchanged`
- [x] `it still returns 400 for InvalidRouteParameterException`
- [x] `it resolves the renderer from the container so a Preference can replace it`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

