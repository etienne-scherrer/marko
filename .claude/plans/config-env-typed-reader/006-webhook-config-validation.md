# Task 006: Validate webhook max_retries, retry_delay and timestamp_tolerance

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
#293 validated only `webhook.timeout`. Reject a negative `max_retries`, a negative `retry_delay` and a non-positive `timestamp_tolerance` in `WebhookConfig` with a `ConfigException` naming the key.

## Context
- Related files: packages/webhook/src/Config/WebhookConfig.php, packages/webhook/tests

## Requirements (Test Descriptions)
- [x] `it throws a ConfigException naming webhook.max_retries when it is negative`
- [x] `it throws a ConfigException naming webhook.retry_delay when it is negative`
- [x] `it throws a ConfigException naming webhook.timestamp_tolerance when it is zero or negative`
- [x] `it accepts zero retries and zero retry delay`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
