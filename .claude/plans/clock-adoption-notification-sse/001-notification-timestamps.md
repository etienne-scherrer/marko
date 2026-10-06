# Task 001: Notification timestamps via clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`DatabaseChannel` (marko/notification) and `DatabaseNotificationRepository` (marko/notification-database) stamp `created_at`/`read_at` with `date('Y-m-d H:i:s')`. Inject `ClockInterface` and format `$clock->now()` instead.

## Context
- Related files: packages/notification/src/Channel/DatabaseChannel.php, packages/notification-database/src/Repository/DatabaseNotificationRepository.php, their tests and composer.json
- Patterns to follow: packages/session (required trailing ClockInterface constructor parameter)

## Requirements (Test Descriptions)
- [x] `it stamps created_at from the injected clock when sending one notification`
- [x] `it stamps created_at from the injected clock for every row of a batch send`
- [x] `it stamps read_at from the injected clock when marking a notification as read`
- [x] `it stamps read_at from the injected clock when marking all notifications as read`
- [x] both composer.json files require marko/clock
- [x] notification-database composer.json adds `"marko/testing": "self.version"` to `require-dev` (it currently has only pest, but the tests use FakeClock)

Note: `tests/Unit/Channel/DatabaseChannelClockTest.php` and `tests/Unit/Repository/DatabaseNotificationRepositoryClockTest.php` may already exist in the worktree. Extend them instead of duplicating them, and update every existing `new DatabaseChannel(...)` / `new DatabaseNotificationRepository(...)` in the older tests.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
