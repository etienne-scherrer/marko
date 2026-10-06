# Task 001: Exception hierarchy in marko/database

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `QueryException extends DatabaseException`, `ConstraintViolationException extends QueryException`, and its four subclasses. Unique and FK violations implement `HttpExceptionInterface` (409, generic body that never names the constraint).

## Context
- Related files: packages/database/src/Exceptions/, packages/database/src/Exceptions/EntityNotFoundException.php (HTTP pattern)
- Patterns to follow: MarkoException named params; static factories

## Requirements (Test Descriptions)
- [x] `it carries the sql, bindings and previous PDOException`
- [x] `it redacts string bindings from the fallback message`
- [x] `it drops driver DETAIL lines that carry row data from the message`
- [x] `it exposes constraint name, table and column on constraint violations`
- [x] `it builds a unique violation message naming the constraint and table with a suggestion`
- [x] `it maps unique and foreign key violations to 409 with a generic body`
- [x] `it keeps not-null and check violations out of the HTTP mapping`
- [x] `it does not corrupt the message when a short or numeric string binding appears in it` (e.g. binding `"1"` must not mangle `SQLSTATE[42P01]`; binding `"a"` must not mangle words)

## Shared Contract (tasks 002, 003, 004, 005, 007 build against this)
- `QueryException::fromDriverError(PDOException $previous, string $sql, array $bindings): self`. Accessors: `sql()`, `bindings()`, `sqlState()`, static `sqlStateOf(PDOException)`.
- `ConstraintViolationException` constructor: `(message, sql, bindings, sqlState, constraintName, table, column, context, suggestion, previous)`. Accessors: `constraintName()`, `table()`, `column()`, all `?string`.
- Each of the four subclasses has `static fromDriverError(PDOException $previous, string $sql, array $bindings, ?string $constraintName = null, ?string $table = null, ?string $column = null): self`, and builds its message via `describe()` (never from driver text).
- `table()` is the table that owns the constraint. For FK violations that is the child (referencing) table, on both insert and delete.
- Never pass the PDOException's (string) code to `MarkoException`'s `int $code`.

## Acceptance Criteria
- All requirements have passing tests
- No new composer dependency on routing/errors

## Implementation Notes
- Redaction: always replace quoted occurrences (`'v'`, `"v"`) of every string or JSON-encoded binding. Replace bare occurrences only for values of 4 or more characters, so short IDs bound as strings cannot corrupt the driver text.
