# Devil's Advocate Review: migrate-safety

## Critical (Must fix before building)

1. **Existing tests construct the commands with `isProduction:` (001, 006).** Named `isProduction:` args appear in
   RollbackCommandTest (14), SeedCommandTest (7), SeederRunnerTest (7), SeederExecutionTest (5), RebuildCommandTest,
   MigrateCommandTest (helper at line ~302 plus direct `new MigrateCommand(...)` at ~689/~735). Removing the bool
   breaks all of them. Tasks did not list these files. Fix: list them in 001/006 and require each call site to switch
   to `new AppEnvironment(['APP_ENV' => ...])`. Existing MigrateCommand generation tests relied on the old default
   (dev); they must now pass a development environment explicitly.
2. **New options not declared as flags (006, 007).** `#[Command(... flags: ['no-generate', 'verbose', 'v'])]`:
   `generate` and `force` must be added, otherwise `--force` can swallow the next token as its value.
3. **Integration partial-index test cannot pass as written (008).** A hand-made index not declared on the entity is
   dropped by design unless listed in `#[Table(unmanagedIndexes:)]` or `migrations.ignore_indexes`. 008 must say the
   fixture declares it (config key preferred, so the test exercises the module.php binding end to end).
4. **HarnessTest requires `issue(170)` to remain in the source (008).** HarnessTest greps `*Test.php` for
   `issue(: |\()170`. Flipped tests must be tagged `->issue(170)`.

## Important (Should fix before building)

5. **Parallel `module.php` edits (001, 003, 007).** 001 and 003 have no dependency between them; 003 and 007 neither.
   All three edit the `bindings` array. Serialize: 003 depends on 001; 007 depends on 003.
6. **`Schema\Table::with*()` rebuild the table (003).** `withColumn/withIndex/withForeignKey` call `new self(...)`;
   each must carry `unmanagedIndexes` or extender merges in SchemaRegistry silently lose it.
7. **`DatabaseConfig` has two construction paths (003).** `fromArray()` sets properties by reflection from its own
   `$props` list; `ignoreIndexes` must be added to both the constructor and `fromArray()`, with the same validation.
8. **Destructive detection already exists (007).** `SchemaDiff::hasDestructiveChanges()/getDestructiveChanges()`
   include FOREIGN KEY drops too. Use them; do not re-derive from SQL. Plan text said only DROP COLUMN / DROP INDEX.
9. **Prompter output and non-interactive detection (007).** devai's `StdinPrompter` writes the question to STDOUT,
   bypassing the command `Output` (unit tests capture a memory stream). Command should print the statement list and
   question via `Output`; the prompter only reads. Non-interactive = `!stream_isatty(STDIN)`. Default answer is No.
   Exit codes were undefined: non-interactive refusal must exit 1 (loud in CI) with no file written; user decline
   exits 0 with "Migration generation cancelled." Pending files applied earlier stay applied.
10. **Drift warning only prints when nothing was applied (006).** Current code checks drift only inside
    `if ($schemaCount === 0 && $dataCount === 0)`. A production deploy that applies files and still drifts would be
    silent. Run the drift check after applying, whenever generation is skipped by environment.
11. **Integration env selection (008).** `AppEnvironment` reads `MARKO_ENV` before `APP_ENV`, lazily on every call,
    from `$_ENV` then `getenv()`. Tests must set (and restore in `finally`) both `$_ENV` and `putenv` for `APP_ENV`,
    and clear `MARKO_ENV`, or a CI-level `MARKO_ENV` wins. Default integration env is unset = production.
12. **`SeederRunner` constructor shape (001).** Specify `array $seeders, AppEnvironment $appEnvironment,
    ?TransactionInterface $transaction = null` (required, no default) so the closure binding and tests are explicit.

## Minor (Nice to address)

- Drift listing on MySQL calls `generateUp()`; an entity with `where` will throw from 005 inside the warning path.
  Acceptable (loud), but the message should come from the generator exception, not a stack trace.
- `pg_indexes.indexdef` wraps the predicate in parentheses (`WHERE (deleted_at IS NULL)`); prefer
  `pg_get_expr(indpred, indrelid)` or strip one outer pair. Name-based diff means this is cosmetic.
- `Index::equals()` comparing `where` will make `Table::equals()` false for pgsql-normalised predicates; harmless
  today since DiffCalculator is name-based.

## Questions for the Team

- Should `--no-generate` in development also print the drift listing? (Plan: only environment-skipped runs.)
- Should staging (neither production nor development) allow `db:rebuild` / `db:seed`? Plan: yes (only production
  blocks), which matches `AppEnvironment::isProduction()`.
