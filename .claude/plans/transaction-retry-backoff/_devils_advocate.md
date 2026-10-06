# Devil's Advocate Review: transaction-retry-backoff

## Critical (Must fix before building)

1. **Task 001 breaks the whole suite unless it also changes the production implementers.** Adding `$backoff` to `TransactionInterface::transaction()` makes `MySqlConnection`, `PgSqlConnection` and `ReadWriteConnection` fatal at class load ("Declaration must be compatible"). Tasks 003, 004 and 005 run after 001, so their workers would start on a red suite. Fix: 001 also adds the parameter to all three production signatures (RW forwards it, drivers accept it and validate it for now). 003, 004 and 005 then add the real behaviour.
2. **The seam's API was never defined.** Tasks 002, 003 and 004 run in parallel against `SleeperInterface` and `TransactionBackoff`, but nothing pins down method names, units, constructor shape or exception names. Fix: a contract section in `_plan.md`.
3. **Containers that resolve a driver connection without `marko/database`'s module will throw.** `Container::resolve()` ignores defaults for class-typed params, so `TransactionBackoff`, then `SleeperInterface`, gets resolved, and there's no binding for it. `packages/database-pgsql/tests/Module/DialectOverrideTest.php::buildVariantContainer()` registers only the pgsql manifest and resolves `ConnectionInterface` to `PgSqlConnection`, so it will fail. Fix: 004 updates that test to also register `marko/database`'s bindings. 003 and 004 audit every container-built test that resolves a driver connection.

## Important (Should fix before building)

1. **The connection factories bypass the container.** `MySqlConnectionFactory::make()` and `PgSqlConnectionFactory::make()` call `new XConnection($config, $charset)`. `database-readwrite/module.php` builds its write connection through the factory, so the write connection always gets the default backoff, and a Preference on `SleeperInterface` is silently ignored there. Fix: inject `TransactionBackoff` into both factories and pass it through (03 and 04).
2. **The driver constructors need the parameter to be optional and last.** About 40 anonymous subclasses call `parent::__construct($config)`, and some pass `exceptionTranslator:` by name. The new parameter must therefore be appended after `$exceptionTranslator` with a `new TransactionBackoff(new UsleepSleeper(), new Randomizer())` default. Fix: contract in `_plan.md`.
3. **Task 006 is half done and collides with 003.** The 1020 mapping is already in `MySqlExceptionTranslator` (line 85), and `MySqlExceptionTranslatorTest` already has "translates error 1020 into a SerializationFailureException". The remaining retry test goes in `MySqlConnectionRetryTest.php`, which 003 is rewriting at the same time. Fix: 006 depends on 003, covers only the retry test, and uses `FakeSleeper` or `backoff: 0`.
4. **A closure that returns a non-int is not covered.** A `Closure` can return anything at runtime. Fix: 001 adds a loud-failure requirement for a non-int return.
5. **The plan's status is inconsistent.** The task table marks every task `completed`, but the task files say `pending` and the code isn't implemented. An orchestrator could skip everything. Fix: reset the table to `pending`.
6. **The docs example will go stale.** `packages/docs-markdown/docs/packages/testing.md` contains a `function transaction(` implementation example with the old signature. Fix: 007 updates it.

## Minor (Nice to address)

- The success criterion quotes `random_int(...)`, but the implementation uses `Randomizer::getInt()` so the delay can be seeded. The wording should say that.
- `marko/testing` has `marko/database` only in require-dev/suggest. `FakeSleeper` implements a `marko/database` interface, so the suggest text should mention it.
- The existing retry tests in 003, 004 and 005 will start sleeping for real (up to about 30 ms per test). Switching them to `backoff: 0` keeps the suite fast.

## Questions for the Team

- Should `ReadWriteConnection` keep forwarding `null` (the driver default)? Or should the factory-built write connection share one container-resolved `TransactionBackoff`? With Important #1 applied, it shares one.
- Is a MariaDB CI service for a real 1020 integration test planned for a specific release?
