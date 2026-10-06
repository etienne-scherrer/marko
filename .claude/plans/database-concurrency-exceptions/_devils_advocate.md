# Devil's Advocate Review: database-concurrency-exceptions

## Critical (Must fix before building)

### C1. Task 002 leaves the suite unloadable (tasks 002, 005, 006, 007)
Task 002 changes `TransactionInterface::transaction()` to `(callable $callback, int $attempts = 1)` but explicitly excludes `PgSqlConnection`, `MySqlConnection` and `ReadWriteConnection`. In PHP, an implementor with fewer parameters than the interface is a fatal "Declaration must be compatible" error. Until 005/006/007 land, any test that autoloads those classes fails. Task 002 can't meet its own acceptance criterion ("Whole suite loads"), and the 003/004 workers running in parallel hit fatals when they run `composer test`.
**Fix applied:** Task 002 now widens the signatures of both drivers (they accept the parameter and validate it, with no retry yet) and absorbs task 007. `ReadWriteConnection` forwarding is a one-line change plus tests. Task 007 is marked merged.

### C2. MySQL `PDO::rollBack()` throws after a deadlock, which masks the conflict at the OUTERMOST level (task 006, 009)
MySQL ends the whole transaction server-side on 1213. pdo_mysql's in-transaction check reads the server status flag (`SERVER_STATUS_IN_TRANS`), and since PHP 8.0 `PDO::rollBack()` throws `PDOException: There is no active transaction` when that check is false. `MySqlConnection::rollback()` calls `$this->pdo->rollBack()` at level 1 without a guard (lines 324-325; only `commit()`'s failure path checks `inTransaction()`). So in `transaction()` the `catch` calls `rollback()`, that throws a raw `PDOException`, and the `DeadlockException` is replaced. Nothing gets retried, and `discard()` drops the after-rollback callbacks even though the data was rolled back.
The plan only covers the nested `ROLLBACK TO SAVEPOINT` case. The outermost case breaks every MySQL deadlock retry, including task 009's integration test.
**Fix applied:** Task 006 now requires guarding the level-1 `rollBack()` with `inTransaction()` (the level then closes normally and its after-rollback callbacks run). It also requires that a rollback failure that follows a conflict never masks the conflict. A unit test was added that uses a fake PDO whose `inTransaction()` is false and whose `rollBack()` throws.

## Important (Should fix before building)

### I1. Behaviour contract for 005 and 006 is underspecified, so two parallel workers will diverge
Parallel workers will choose differently on several points, and the docs (010) need one answer:
- After-rollback callbacks of a failed attempt: do they run on each attempt? `rollback()` runs them today, so the natural answer is yes. Make it explicit and test it.
- `$attempts < 1` validation: is it done before `beginTransaction()`, and also on nested calls? Specify "always, before opening anything".
- The SQL string passed to the translator for COMMIT / RELEASE / ROLLBACK TO failures. Specify the literal statement (`'COMMIT'`, `'RELEASE SAVEPOINT marko_sp_N'`, `'ROLLBACK TO SAVEPOINT marko_sp_N'`).
- Transaction level must be 0 before the next attempt starts, even when rollback itself failed.
**Fix applied:** These are added to the `_plan.md` Architecture Notes and as requirements in 005/006.

### I2. RefreshDatabase / TestDatabase makes every app `transaction()` call nested (task 010)
`TestDatabase` wraps each test in an outer transaction, so `transaction($cb, attempts: 3)` in application code never retries under `RefreshDatabase`. Users will be confused when they try to test their retry path.
**Fix applied:** Task 010 now has to document this and point users at testing retries against a non-wrapped connection.

## Minor (Nice to address)
- `_plan.md` says `TransactionConflictException` is "abstract-free (a concrete base)", but the class already in the worktree is `abstract`. That is fine for `catch`, but the note is wrong.
- Task 001 looks already implemented in the worktree (`TransactionConflictException`, `DeadlockException`, `SerializationFailureException`, `LockTimeoutException`, `TransactionException::invalidAttempts()` exist), yet it is still `pending`. The orchestrator should verify it rather than rebuild it.
- The `TransactionConflictException` docblock says "the database has already rolled the work back". On PostgreSQL the transaction is only *aborted* until ROLLBACK is issued, and inside a savepoint the outer transaction can continue after `ROLLBACK TO SAVEPOINT`. Consider rewording.
- MySQL reports 1213 with SQLSTATE `40001`, so `DeadlockException::sqlState()` on MySQL returns the "serialization failure" SQLSTATE. Worth a docs footnote.
- `beginTransaction()` (BEGIN / SAVEPOINT) failures are still untranslated `PDOException`s in both drivers.
- When a nested `ROLLBACK TO SAVEPOINT` fails, `discard()` drops that level's after-rollback callbacks without running them, even though the outer rollback undoes the data. This behaviour predates the plan.
- Contender scripts under `tests/Fixtures/Concurrency/` must forward the `MARKO_TEST_*` env vars to `proc_open()` and locate the autoloader. They may also need a PHPStan exclusion if they use `$argv`-style globals.

## Questions for the Team
- Should the retry loop add any back-off at all (even a fixed few ms)? Immediate retries after a deadlock often hit the same conflict under contention. Back-off is currently out of scope by design.
- Should an after-commit callback that itself throws a `TransactionConflictException` (from its own inner, outermost `transaction()`) be documented as "never retried by the enclosing call"? The plan already tests this, but the docs should state it.
