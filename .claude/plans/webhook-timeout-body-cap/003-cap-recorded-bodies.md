# Task 003: Cap every recorded response body in WebhookDeliveryService

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`recordSuccess()` stores the raw body, which can overflow the `TEXT` column after the webhook was already delivered. Extract a private `excerpt()` helper built on `HttpResponse::bodyExcerpt()` and use it in both `recordSuccess()` and `recordRejection()`.

## Context
- Related files: packages/webhook/src/Sending/WebhookDeliveryService.php, packages/webhook/tests/Sending/WebhookDeliveryServiceTest.php

## Requirements (Test Descriptions)
- [x] `it caps the response body of a successful attempt at 500 bytes`
- [x] `it stores a short successful response body unchanged`
- [x] `it trims surrounding whitespace from a successful response body` (`bodyExcerpt()` trims, so `" OK\n"` is stored as `"OK"`; pin this behavior change)
- [x] Existing rejection cap test still passes

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
