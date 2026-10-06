# Task 006: CI MariaDB steps and testing docs

**Status**: completed
**Depends on**: 003, 004, 005
**Retry count**: 0

## Description
Run the new MySQL suites (queue-database, session-database and the fresh db:migrate fixture) against MariaDB 11.8 and 10.11 in the CI Integration job, and document them in .claude/testing.md.

## Context
- Related files: .github/workflows/ci.yml, tests/CiWorkflowTest.php, .claude/testing.md

- Append these paths to both MariaDB steps' pest command (alongside `packages/database-mysql/tests/Integration packages/admin-auth/tests/Integration/MySql`): `packages/queue-database/tests/Integration/MySqlRoundTripTest.php`, `packages/session-database/tests/Integration/MySql`, and Task 005's MySQL fixture test file. Update the step names, which currently say "MySQL driver integration suite"

## Requirements (Test Descriptions)
- [ ] `it runs the queue and session MySQL suites against both MariaDB services`

## Acceptance Criteria
- CiWorkflowTest passes

## Implementation Notes
