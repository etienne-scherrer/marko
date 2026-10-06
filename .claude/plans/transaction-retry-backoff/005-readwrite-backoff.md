# Task 005: ReadWriteConnection passes backoff through

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
`ReadWriteConnection::transaction()` forwards `$backoff` to the write connection, and the backoff tests run through the decorator over a real driver connection.

## Context
- Related files: packages/database-readwrite/src/Connection/ReadWriteConnection.php
- 001 already added the forwarding signature. This task proves the behaviour end to end: a ReadWriteConnection wrapping a MySqlConnection built with `new TransactionBackoff($fakeSleeper, new Randomizer(new Mt19937(<seed>)))` (the ScriptedPdo-based subclass from 003).
- Also cover the module path: a write connection made through `ConnectionFactoryInterface` in `database-readwrite/module.php` must get the container-bound `TransactionBackoff` (relies on the factory change in 003).

## Requirements (Test Descriptions)
- [x] `it passes the backoff to the write connection`
- [x] `it waits the default jittered delay between attempts through the write connection`
- [x] `it retries immediately through the write connection when backoff is zero`
- [x] `it honours an int and a closure backoff through the write connection`
- [x] `it never sleeps in a nested transaction through the write connection`
- [x] `it gives the factory-built write connection the container-bound TransactionBackoff`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Pass-through asserted with the recording fake in ReadWriteConnectionTest; end-to-end tests in ReadWriteConnectionBackoffTest wrap MySqlConnections over in-memory SQLite PDOs. The factory-built write connection gets the bound backoff (covered by the driver SharedConnectionTest factory tests).
