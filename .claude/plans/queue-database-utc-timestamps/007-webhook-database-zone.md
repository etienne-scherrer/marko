# Task 007: Webhook attemptedAt in the database zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Inject `DatabaseTimezoneConfig` into `WebhookDeliveryService` and format attemptedAt through it for success, rejection and failure. Declare `marko/database` in webhook's composer.json require (the package already extends `Entity`).

## Context
- Related files: packages/webhook/src/Sending/WebhookDeliveryService.php, packages/webhook/composer.json, packages/webhook/tests/Sending, packages/webhook/tests/Jobs
- Constructor: `DatabaseTimezoneConfig $databaseTimezoneConfig` goes immediately after `ClockInterface $clock`. Positional `new WebhookDeliveryService($repo, new FakeClock())` call sites in tests/Jobs (DispatchWebhookJobTest, DispatchWebhookJobRetryTest x2, DispatchWebhookJobResponseTest, SerializableWebhookJobTest) all need the new argument.

## Requirements (Test Descriptions)
- [x] `it records a successful attempt time in the database timezone`
- [x] `it records a rejected attempt time in the database timezone`
- [x] `it records a failed attempt time in the database timezone`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
WebhookDeliveryService takes `DatabaseTimezoneConfig` after the clock; marko/database declared in webhook require.
