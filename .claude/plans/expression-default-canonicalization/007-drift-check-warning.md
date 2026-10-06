# Task 007: Warn instead of failing when the post-migration drift check cannot probe

**Status**: completed
**Depends on**: 003, 004, 005
**Retry count**: 0

## Description
Coordinator decision on the PR's open question. Outside development, `db:migrate` applies the pending migrations and then runs a drift check. If the expression default probe fails there (rejected expression, or no temporary table privilege), the migrations already succeeded, so the command must warn on STDERR and exit with the migrations' own status. `db:diff`, and `db:migrate` in development (which generates migrations), stay loud.

## Context
- Related files: packages/database/src/Command/MigrateCommand.php, packages/database/src/Exceptions/ExpressionDefaultProbeException.php, packages/core/src/Command/ErrorOutput.php
- The probe failure gets its own `ExpressionDefaultProbeException` (a `MigrationException`), so `reportDrift()` can tell it apart from other migration errors.

## Requirements (Test Descriptions)
- [x] `it writes to STDERR by default` (ErrorOutput)
- [x] `it names the table, column, expression and database error` (ExpressionDefaultProbeException)
- [x] `it suggests fixing the expression or granting the temporary table privilege`
- [x] `it warns on stderr and exits with the migration status when the post-migration drift check fails`
- [x] `it still fails db:migrate in development, where it generates migrations`
- [x] `it fails loudly with the probe error naming the column when the database rejects the expression` (DiffCommand)

## Acceptance Criteria
- All requirements have passing tests; docs describe both behaviours

## Implementation Notes
`ErrorOutput` (core) is an `Output` defaulting to STDERR, autowired into `MigrateCommand`. `MigrationException::rejectedDefaultExpression()` was replaced by `ExpressionDefaultProbeException::rejected()`, whose message now includes the database error.
