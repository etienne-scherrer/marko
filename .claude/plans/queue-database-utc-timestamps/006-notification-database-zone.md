# Task 006: Notification created_at / read_at in the database zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Inject `DatabaseTimezoneConfig` into `DatabaseChannel` (created_at, single and batch) and `DatabaseNotificationRepository` (read_at for markAsRead / markAllAsRead).

## Context
- Related files: packages/notification/src/Channel/DatabaseChannel.php, packages/notification-database/src/Repository/DatabaseNotificationRepository.php and their tests
- Constructors: insert `DatabaseTimezoneConfig $databaseTimezoneConfig` immediately after `ClockInterface $clock`. Both classes are autowired (notification module.php `get(DatabaseChannel::class)`; notification-database binds a class string), so no module changes.

## Requirements (Test Descriptions)
- [x] `it stores created_at in the database timezone whatever the clock timezone`
- [x] `it stores created_at in the database timezone for batched notifications`
- [x] `it stores read_at in the database timezone when marking one notification read`
- [x] `it stores read_at in the database timezone when marking all notifications read`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
DatabaseChannel and DatabaseNotificationRepository take `DatabaseTimezoneConfig` after the clock; the clock tests now use a New York clock with a UTC database zone.
