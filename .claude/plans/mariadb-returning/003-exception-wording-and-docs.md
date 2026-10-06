# Task 003: RepositoryException wording and docs

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`RepositoryException::generatedKeyNotReadable()` no longer claims MySQL-family servers lack RETURNING across the board. The docs say MariaDB 10.5+ reads generated keys back.

## Context
- Related files: packages/database/src/Exceptions/RepositoryException.php, packages/database/tests/Exceptions/RepositoryExceptionTest.php (new; sibling exception tests live in that directory), packages/docs-markdown/docs/packages/database.md, packages/docs-markdown/docs/packages/database-mysql.md, packages/docs-markdown/docs/packages/roadrunner-state-leaks.md
- Keep the message substring `'<driver>' connection cannot read a generated key back` unchanged: RepositoryTest.php:2519, RepositoryBatchInsertTest.php:1124 and database-mysql GeneratedPrimaryKeysTest.php:83 match on it. Change only the context/suggestion.
- The driver name for MariaDB is still `'mysql'`, so the suggestion must not imply the server is MySQL: name the servers that can read keys back (PostgreSQL, MariaDB 10.5+) and say MySQL and MariaDB before 10.5 cannot.
- Docs to correct (each currently states MySQL-family / `marko/database-mysql` never supports RETURNING, or that `supportsReturning()` needs no live connection):
  - database.md ~line 215 (Database-generated keys: drop "treats MariaDB like MySQL for now")
  - database.md ~line 1496 (insertBatch: MariaDB 10.5+ now in the RETURNING branch)
  - database.md ~lines 1897 and 1912 (6-binding split / Implementing ConnectionInterface: `supportsReturning()` may query the server once; it no longer belongs in the "work without a live connection" list)
  - database-mysql.md ~line 53 and the API table ~line 223 (`supportsReturning()` no longer "Always false"; add `server(): MySqlServer`)
  - roadrunner-state-leaks.md ~line 66 (`MySqlServer` is now owned by `MySqlConnection`; note the connection keeps it across requests, same "Leaks: No" reasoning)

## Requirements (Test Descriptions)
- [x] `it names PostgreSQL and MariaDB 10.5+ as the servers that read generated keys back`
- [x] `it suggests setting the key in PHP`
- [x] `it keeps the driver name and entity class in the message`
- [x] Docs updated (no test)

## Acceptance Criteria
- Exception test passes; docs accurate

## Implementation Notes
Suggestion now names PostgreSQL and MariaDB 10.5+; message unchanged. Docs updated in database.md, database-mysql.md and roadrunner-state-leaks.md.
