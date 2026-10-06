# Task 003: queue-database: DatabaseQueue reads the clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`DatabaseQueue` takes a required `ClockInterface` used for push, pop/reserve (reclaim cutoff), size and release, so delays and reservation expiry can be tested by moving a FakeClock.

## Context
- Related files: packages/queue-database/src/DatabaseQueue.php, packages/queue-database/module.php, composer.json, tests

## Requirements (Test Descriptions)
- [ ] `it stores available_at and created_at from the injected clock`
- [ ] `it does not pop a delayed job until the clock reaches its available time`
- [ ] `it reclaims a reserved job once the clock passes retry_after`
- [ ] `it counts only jobs available at the clock time in size`
- [ ] `it schedules a released job relative to the injected clock`
- [ ] `it stamps failedAt from the injected clock when a reclaimed job exhausts its attempts`
- [ ] `it passes the container clock to DatabaseQueue in the module binding`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Insert `ClockInterface $clock` after `QueryBuilderFactoryInterface $queryBuilderFactory` and BEFORE `string $table = 'jobs'`.
- `module.php` builds the queue in a closure with `new DatabaseQueue(...)`, so it will NOT autowire the clock. Add `clock: $container->get(ClockInterface::class)`.
- Update every construction site: `tests/DatabaseQueueTest.php`, `tests/DatabaseQueueAttemptsTest.php`, `tests/Integration/PgSqlRoundTripTest.php` (real-driver suites run in CI).
- Read the clock once per operation (`$now = $this->clock->now()`), as today, so cutoff and reserved_at stay consistent within a reservation.
