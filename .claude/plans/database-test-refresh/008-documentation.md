# Task 008: Documentation, READMEs, composer suggest

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 007
**Retry count**: 0

## Description
testing.md database section replacing the manual DatabaseTestHelper recipe; database.md factories note rewritten (when to use factories vs `new`) and after-commit note; READMEs; marko/testing suggests marko/database; DatabaseTestHelper docblock points at RefreshDatabase.

## Requirements (Test Descriptions)
- [ ] `it suggests marko/database without requiring it`

## Acceptance Criteria
- Docs class reference test passes

## Implementation Notes
- testing.md must state the required setup: `<env name="APP_ENV" value="testing"/>` in phpunit.xml (unset APP_ENV = production, which TestDatabase refuses) and a dedicated test database.
- Document that after-rollback callbacks registered by code under test run at teardown, and that TruncateDatabase only touches entity-backed tables.
