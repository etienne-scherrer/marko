# Task 006: Lock and upsert API on QueryBuilderInterface, RepositoryQueryBuilder and exceptions

**Status**: pending
**Depends on**: none
**Retry count**: 0

## Description
Declare lockForUpdate(), sharedLock(), skipLocked(), noWait() and upsert() on QueryBuilderInterface and delegate them from RepositoryQueryBuilder. Add LockException and UpsertException, and update the QueryBuilderInterface test doubles.

## Context
- Related files: packages/database/src/Query/QueryBuilderInterface.php, packages/database/src/Repository/RepositoryQueryBuilder.php, packages/database/src/Exceptions/

- Signatures and semantics: follow "Shared contract" in `_plan.md` exactly. Tasks 007, 008 and 009 build against it in parallel.
- Keep every implementer compiling. Adding interface methods otherwise fatals every test that loads them, and fails PHPStan, until 007 and 008 land.
  - `PgSqlQueryBuilder` and `MySqlQueryBuilder`: add the four lock setters. They store state and return `$this`. Add an `upsert()` that throws `LogicException('Implemented in task 007/008')`. 007 and 008 replace these.
  - `packages/database-pgsql/tests/Fixtures/Variant/VariantQueryBuilder.php`: add the new methods.
  - The ~20 anonymous `QueryBuilderInterface` / `EntityQueryBuilderInterface` doubles under `packages/database/tests/{Query,Entity,Repository}`: add the new methods. Grep `implements QueryBuilderInterface` and `implements EntityQueryBuilderInterface`.

## Requirements (Test Descriptions)
- [ ] `it declares lockForUpdate, sharedLock, skipLocked, noWait and upsert on QueryBuilderInterface`
- [ ] `it delegates lock methods from RepositoryQueryBuilder and returns itself`
- [ ] `it delegates upsert from RepositoryQueryBuilder`
- [ ] `it keeps both driver builders and every test double satisfying QueryBuilderInterface` (full `composer test` + `composer phpstan` green)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
