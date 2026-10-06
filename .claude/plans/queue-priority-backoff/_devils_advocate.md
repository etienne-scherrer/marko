# Devil's Advocate Review: queue-priority-backoff

## Critical (Must fix before building)
- **003 breaks the suite until 004 lands.** `WorkCommandTest.php` has 6 anonymous `implements WorkerInterface` stubs with `work(?string $queue, ...)`; changing the interface signature makes them fatal. `WorkCommand` calls `work(queue: $queue)`, so renaming the parameter throws "Unknown named parameter". Fix: 003 must update those stubs and do a minimal `WorkCommand` adaptation (wrap the string in a one-item list). 004 then adds the comma parsing.
- **001 property type must match the existing fixtures.** `tests/JobTest.php` already redeclares `public protected(set) array|int|null $backoff`. Class properties are invariant, so `Job` must declare exactly that type and visibility.

## Important (Should fix before building)
- **002 never says how the list is indexed.** Use `list[attempts - 1]` (attempts is incremented before `handle()`), clamped to the last entry. The default curve keeps using the post-increment `attempts`.
- **001/002 split validation without a rule.** `QueueConfig::backoff()` should check only the type, using `has()` before `get()` (`get()` throws on a missing key). `backoffFor()` should validate value shape for both job and config: negative values, empty lists, non-int entries and non-list arrays.
- **003 never defines strict priority.** After each job, popping must restart from the first queue. `null` must still call `pop(null)` so default behaviour stays the same.
- **004 does not cover an empty or blank `--queue`.** Segments should be filtered, and an empty result must fail loudly.

## Minor
- `stop()` is only checked between iterations, not between pops.
- The docs example `readonly class SendWelcomeEmail extends Job` is invalid PHP. This bug was already there before this plan.

## Questions for the Team
- An invalid job backoff throws inside `catch` and kills the worker. The job then stays reserved until `retry_after`. Is that acceptable?
