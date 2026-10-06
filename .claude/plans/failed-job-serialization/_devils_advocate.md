# Devil's Advocate Review: failed-job-serialization

## Critical (Must fix before building)

1. **Task 001 breaks an existing test it does not mention.** `packages/queue/tests/AsyncObserverJobTest.php:118-142` ("clears the container and envelope from AsyncObserverJob after handle() so a failed job can still be serialized") asserts that `handle()` alone releases the container: the second `handle()` must throw "without a container". When the per-job `finally` is dropped, that assertion fails. Task 001 must replace the test with the `releaseContainer()` version.
2. **Task 001: a fixture class in the same file must implement the new method.** `ContainerAwareCaptureJob` in `packages/queue-sync/tests/SyncQueueTest.php:36` implements `ContainerAwareJobInterface`. Until it has `releaseContainer()`, PHP raises a fatal error when the file loads, and that takes down the whole queue-sync suite. Every implementer, test fixtures included, has to change in the same task.
3. **Parallel tasks write to the same files.**
   - 001 and 005 both edit `packages/queue-sync/tests/SyncQueueTest.php`, and 005 has no dependencies.
   - 002 and 004 both edit `packages/queue/tests/Command/RetryCommandTest.php`, and 002 also edits `RetryCommand.php`.
   - Fix: 005 now depends on 001, and 004 now depends on 002.

## Important (Should fix before building)

4. **Anonymous job classes can never be serialized.** Final-failure and round-trip tests in tasks 001 and 004 must use named job classes, declared at the top of the test file or under `tests/Fixtures`. Otherwise the payload fails to serialize for an unrelated reason. Once task 002 lands, the fallback would hide that, and the tests would pass while proving nothing. The 001 test must also assert that the stored payload unserializes to the job class, not to the placeholder array.
5. **Task 002 does not define the fallback contract.** Workers on 002 and 006 need the same shape:
   - The `try`/`catch (Throwable)` wraps only `$job->serialize()`. `JobEnvelope::wrap()` keeps throwing `SerializationException` for an empty signing key, so a config error still fails loudly.
   - Placeholder: `serialize(['class' => $job::class, 'serialization_error' => $e->getMessage()])`, wrapped in the envelope as usual.
   - `exception` text: the original failure message and trace, then a line naming the serialization failure.
6. **Task 002 does not define how retry refuses a placeholder.**
   - Detect it with a generic `!$job instanceof JobInterface` check after unwrapping. That also covers `__PHP_Incomplete_Class` and `false`.
   - Refused rows are not deleted from `failed_jobs`.
   - A single ID returns 1 with a message that names the class and the serialization error.
   - `--all` pushes the retryable jobs, reports how many were skipped, and returns 1 if any were skipped (loud errors).
7. **Task 005 misses `packages/queue-sync/tests/ModuleTest.php`.** Its test "module.php binds QueueInterface via factory" already asserts that `SyncQueue` is bound directly. That is the natural place for the "without a factory" requirement: rename the test there instead of adding a duplicate.
8. **`releaseContainer()` must be idempotent and safe before anything is injected.** Task 001 should say so: it is called in a `finally` that may run before `setContainer()` has.

## Minor (Nice to address)

- Task 004 "stores the failed container-aware job payload without the container" overlaps with task 001's first test.
- Task 005 "it ships no SyncQueueFactory class" is a `class_exists` check of limited value. Deleting the file and grepping for references is enough.
- `FailedCommand::extractJobClass()` shows "Unknown" for every normal (object) payload. Only placeholders show a class. That is pre-existing and out of scope.
- The worktree already contains in-progress edits: `ContainerAwareJobInterface::releaseContainer()` and the notification final-failure tests. Workers should check the current state before writing.

## Questions for the Team

- Should `queue:retry --all` return 1 when it skipped placeholder rows? Applied as yes (loud errors); revert if you prefer 0 with a warning.
