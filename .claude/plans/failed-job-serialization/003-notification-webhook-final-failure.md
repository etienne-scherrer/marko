# Task 003: Notification and webhook final-failure worker tests

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Drive `SendNotificationJob` and `DispatchWebhookJob` through `Worker::work()` with a container that cannot be serialized and fails every resolution, until the final attempt, and prove the job is stored as failed and the worker keeps running.

## Context
- Related files: packages/notification/tests/Unit/SerializableNotificationJobTest.php, packages/webhook/tests/Jobs/SerializableWebhookJobTest.php
- An anonymous-class container cannot be serialized, which reproduces the crash.

## Requirements (Test Descriptions)
- [ ] `it stores a SendNotificationJob that fails for the last time in the failed-job repository and keeps the worker running`
- [ ] `it stores a DispatchWebhookJob that fails for the last time in the failed-job repository and keeps the worker running`
- [ ] `it releases the container from SendNotificationJob when releaseContainer is called`
- [ ] `it releases the container from DispatchWebhookJob when releaseContainer is called`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
