# Devil's Advocate Review: database-generated-keys

## Critical (Must fix before building)

1. **Task 002 misses a stub outside `packages/`.** `tests/Integration/QueryBuilderRawConsistencyTest.php` (repo root) has `new class () implements ConnectionInterface` with no `supportsReturning()`. Adding the abstract method makes that file fatal, so the whole suite fails. Task 002 only says to grep under `packages/`. Fix: grep the repo root `tests/` as well, and list this file explicitly.

## Important (Should fix before building)

2. **ReadWriteConnection loses read-your-writes after an `INSERT ... RETURNING` (task 002/004).** `ReadWriteConnection::query()` sends INSERT to the write connection but does not set `$stickyWrite`. Only `execute()` does. Today `save()` calls `execute()`, which makes the connection sticky. After this change, a generated-key `save()` calls `query()`, so the next `find($id)` can go to a lagging replica and return null. Fix: in task 002, set `stickyWrite = true` in `query()` when `isWriteStatement()` is true, and add a test.

3. **`upsert()` shares `extractBatchRow()` with `insertBatch()` (task 005).** If task 004's helper also runs in upsert, it would:
   - throw for an unset generated key on MySQL, even though upsert never reads keys back and the database default fills the key;
   - throw for unset/null non-generated keys, which changes how upsert behaves.

   Fix: upsert strips an unset/null generated key no matter what `supportsReturning()` says, and it never throws the "no RETURNING" error. Add tests for this.

4. **A batch with some generated keys set and some unset (task 005).** Rows with the key and rows without it hit `BatchInsertException::columnSetMismatch`, and that message doesn't explain the cause. Fix: add a requirement for this case: either a clear error, or a rule that the key column is omitted only when every key in the batch is unset.

5. **The behaviour change in task 004 can break tests in other packages.** Before, an unset/null non-AI key reached the database. Now it throws. Entities in admin-auth, notification, session-database, queue-database and `database/Testing/EntityFactory` may save that way. Fix: task 004 must run the full `composer test`, not only the database package.

6. **Task 006 table setup.** If the integration tests build their tables through the schema generator or migrations, they depend on #306/#307. Fix: create the tables with raw DDL (`DEFAULT gen_random_uuid()`; on MySQL, `DEFAULT (UUID())` on `CHAR(36)`).

7. **Task 007 leaves two docs passages out of date.** `database.md` line ~183 says "`save()` doesn't read a generated key back", and line ~1401 says "PostgreSQL uses RETURNING". Both are wrong after this change. Fix: list both in task 007.

## Minor (Nice to address)

- Between task 003 and task 005, `extractBatchRow()` reads `$data[$pkColumn]` for an uninitialized auto-increment key, which raises an undefined-key warning. Task 005 removes it, so this only matters while the tasks are mid-build.
- The `ConnectionInterface` docblock/implementations for task 002 already appear to be in this worktree. The worker should check what is already there before redoing it.
- `insertBatch()` reads the key back inside `transaction()`, which restores the previous sticky state on exit. That is existing behaviour and is not made worse here.

## Questions for the Team

- `generated` may be confused with SQL "generated columns" (`GENERATED ALWAYS AS`) and identity columns. Is the name final?
- Task 007 overlaps with the doc-updater agent in the post-implementation pipeline. Should we keep a dedicated docs task?
