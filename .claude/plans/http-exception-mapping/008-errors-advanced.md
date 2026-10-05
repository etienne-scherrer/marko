# Task 008: errors-advanced resolvable, production-safe and status-correct

**Status**: completed
**Depends on**: 007
**Retry count**: 0

## Description
AdvancedErrorHandler resolves from its module bindings, builds its formatter with the real environment, clears buffers, sets 500 and negotiates JSON.

## Context
- Related files: packages/errors-advanced/src/AdvancedErrorHandler.php, packages/errors-advanced/module.php

## Requirements (Test Descriptions)
- [x] `it resolves ErrorHandlerInterface from the module bindings`
- [x] `it renders the generic page in production without paths, source or trace`
- [x] `it sets status 500 in web SAPI`
- [x] `it clears output buffers before rendering`
- [x] `it renders JSON when the client accepts JSON`
- [x] `it uses the status of an HttpExceptionInterface that reaches the handler`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

