# Task 003: Share EntityHydrator; SeederRunner and cross-repository dirty-check tests

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `EntityHydrator::class` to the `singletons` list in `packages/database/module.php` so the dirty-check snapshot survives across repositories and services, and prove `SeederRunner` now receives a `TransactionInterface`.

## Context
- Related files: packages/database/module.php, packages/database/src/Entity/EntityHydrator.php, packages/database/src/Repository/Repository.php
- RelationshipLoader holds no per-instance state, so it needs no change

## Requirements (Test Descriptions)
- [x] `it resolves the same EntityHydrator instance for two repositories`
- [x] `it saves an entity loaded by another repository as an update of only the dirty columns`
- [x] `it passes the shared TransactionInterface to SeederRunner when a driver is installed`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
