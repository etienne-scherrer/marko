# Task 004: Update webhook docs page

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Document that `webhook.timeout` is applied to every delivery and must be positive, and that every recorded response body is capped at 500 bytes.

## Context
- Related files: packages/docs-markdown/docs/packages/webhook.md (Configuration, Sending Asynchronously with Retry, Delivery Tracking, WebhookDispatcher, WebhookDeliveryService, WebhookAttempt, WebhookConfig sections); packages/webhook/README.md (check it is still accurate)
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] Configuration section says the timeout is passed to the HTTP client and must be a positive integer
- [x] WebhookDispatcher section shows the WebhookConfig dependency and the timeout
- [x] WebhookDeliveryService and WebhookAttempt sections describe the body cap on every recorded response
- [x] A timed-out request is documented as a transport failure that is retried
- [x] Recorded bodies are documented as trimmed and capped at 500 bytes (UTF-8 safe, `... [truncated N bytes]` suffix)
- [x] A non-positive `webhook.timeout` is documented as failing loudly wherever `WebhookConfig` is resolved (dispatching and receiving)

## Acceptance Criteria
- Docs match the implemented behavior

## Implementation Notes
(Left blank - filled in by programmer during implementation)
