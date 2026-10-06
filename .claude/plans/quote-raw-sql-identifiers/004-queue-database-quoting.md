# Task 004: DatabaseQueue and DatabaseFailedJobRepository quote table names

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Quote the configurable $table once through a private table() helper and use it in every raw statement; quote failed_jobs in DatabaseFailedJobRepository.

## Context
- Related files: packages/queue-database/src/DatabaseQueue.php, packages/queue-database/src/DatabaseFailedJobRepository.php, packages/queue-database/tests/
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it quotes the jobs table in every raw statement`
- [ ] `it quotes a custom table name`
- [ ] `it quotes the failed_jobs table in every statement`
- [ ] `it passes the unquoted table name to the query builder in reserveNext`

## Gotchas (from review)
- `reserveNext()` uses the query builder (`->table($this->table)`, DatabaseQueue.php:184), which quotes itself. Keep the raw `$this->table` there; only the raw statements (INSERT, the two UPDATEs, SELECT COUNT, both DELETEs, SELECT payload) use the quoted `table()` helper. Passing the quoted name to the builder double-quotes it.
- The test doubles in DatabaseQueueTest/DatabaseFailedJobRepositoryTest quote with `"`, so update existing assertions on exact SQL text to the quoted form; this is expected, not a regression. The SQLite fixture accepts `"`-quoted names, so the round-trip tests should pass unchanged.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
