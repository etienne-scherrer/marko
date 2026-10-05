# Task 006: Adopt clock in webhook

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `ClockInterface` into `WebhookDispatcher` (signature timestamp), `WebhookVerifier` and `WebhookReceiver` (freshness window) and `WebhookDeliveryService` (`attemptedAt`). `InvalidSignatureException::staleTimestamp()` takes the current time instead of calling `time()`.

## Context
- Related files: packages/webhook/src/{Sending,Receiving,Exceptions}, tests, composer.json

## Requirements (Test Descriptions)
- [x] `it signs the payload with the clock timestamp`
- [x] `it accepts a timestamp exactly at the tolerance boundary`
- [x] `it rejects a timestamp one second past the tolerance`
- [x] `it reports the age from the clock in the stale timestamp message`
- [x] `it records attemptedAt from the clock`

## Acceptance Criteria
- All requirements have passing tests
- marko/webhook requires marko/clock
