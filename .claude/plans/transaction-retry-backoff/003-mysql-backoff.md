# Task 003: MySqlConnection backoff between retries

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `TransactionBackoff` into `MySqlConnection` and wait between retry attempts of the outermost transaction.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/src/Connection/MySqlConnectionFactory.php, packages/database-mysql/tests/Connection/MySqlConnectionRetryTest.php, packages/database-mysql/tests/Connection/MySqlConnectionFactoryTest.php
- Constructor: append `private readonly TransactionBackoff $backoff = new TransactionBackoff(new UsleepSleeper(), new Randomizer())` AFTER `$exceptionTranslator` (about 25 anonymous subclasses call `parent::__construct($config)` or pass `exceptionTranslator:` by name).
- `makeRetryMySqlConnection()` gains an optional `?TransactionBackoff` argument and forwards it to the parent constructor. Existing retry tests should pass `backoff: 0` or a FakeSleeper so they don't really sleep.
- `MySqlConnectionFactory` (readonly, `string $charset` default) takes a `TransactionBackoff` (autowired from the module binding) and passes it to every connection. Otherwise the read/write write connection ignores container overrides.
- Container check: any test that resolves `MySqlConnection` through a container must register `marko/database`'s bindings (the container ignores defaults for class types). `SharedConnectionContainer` already does.

## Requirements (Test Descriptions)
- [x] `it waits the default jittered exponential delay between attempts`
- [x] `it retries immediately when backoff is zero`
- [x] `it waits a fixed delay when backoff is an int`
- [x] `it waits the delay a closure returns`
- [x] `it waits before retrying a conflict raised by COMMIT`
- [x] `it never sleeps when attempts is one`
- [x] `it never sleeps in a nested transaction`
- [x] `it rejects a negative backoff before beginning the transaction`
- [x] `it never sleeps after the final failed attempt`
- [x] `it passes the TransactionBackoff to connections made by MySqlConnectionFactory`
- [x] `it resolves MySqlConnection with the bound TransactionBackoff from a container`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
MySqlConnection validates `$backoff` before BEGIN and calls `TransactionBackoff::wait()` in both retry paths (callback conflict and COMMIT conflict). MySqlConnectionFactory passes its backoff to each connection. Container resolution is covered in tests/Module/SharedConnectionTest.php. Existing retry tests keep the real default backoff (a few ms of sleeping).
