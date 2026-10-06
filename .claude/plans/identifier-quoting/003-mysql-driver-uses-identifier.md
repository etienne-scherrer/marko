# Task 003: MySQL generator, query builder and introspector use MySqlIdentifier

**Status**: pending
**Depends on**: 001
**Retry count**: 0

## Description
`MySqlGenerator::quote()` and `MySqlQueryBuilder::quoteIdentifier()` become one-line delegates to `MySqlIdentifier::quote()`, and `MySqlIntrospector`'s hard-coded backticks go through it as well, so no inline backtick identifier quoting is left in the driver.

## Context
- Related files: `packages/database-mysql/src/Sql/MySqlGenerator.php`, `packages/database-mysql/src/Query/MySqlQueryBuilder.php`, `packages/database-mysql/src/Introspection/MySqlIntrospector.php`, `packages/database-mysql/tests/Sql/MySqlGeneratorTest.php`

## Requirements (Test Descriptions)
- [ ] `it escapes a backtick in a table name in CREATE TABLE`
- [ ] `it escapes a backtick in a column name in ADD COLUMN`
- [ ] `it escapes a backtick in index, foreign key and referenced names`
- [ ] `it quotes reserved-word columns in generated DDL`
- [ ] `it has no inline backtick identifier quoting in the generator, query builder or introspector`

## Acceptance Criteria
- All requirements have passing tests; existing generator and builder tests unchanged

## Implementation Notes
- The "no inline backtick quoting" test must target quoting concatenations (e.g. `'`' .`, `` '`%s`' ``), not every backtick: `MySqlIntrospector` legitimately keeps backticks in the `json_valid` CHECK-clause regex and in docblocks. A behavior test (probe SQL through a recording connection) is preferable to a raw source grep.
- `MySqlQueryBuilder::quoteIdentifier()` stays `protected` (Preference extension point) and delegates.
- Do not edit `ConnectionInterface` test stubs in this task (task 005 owns them, to avoid parallel conflicts).
