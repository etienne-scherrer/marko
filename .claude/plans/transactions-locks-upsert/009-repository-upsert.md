# Task 009: Repository::upsert

**Status**: pending
**Depends on**: [005, 006]
**Retry count**: 0

## Description
Add Repository::upsert(array $entities, array $uniqueBy, ?array $update = null): int. It maps property names to columns and delegates to the query builder.

## Context
- Related files: packages/database/src/Repository/Repository.php, packages/database/tests/Repository/

## Requirements (Test Descriptions)
- [ ] `it upserts entities through the query builder with mapped column names`
- [ ] `it excludes the created-at column and auto-increment key from the default update list`
- [ ] `it applies insert timestamps before upserting`
- [ ] `it throws when an unknown property is named in uniqueBy or update`
- [ ] `it throws when no query builder factory is configured`
- [ ] `it rejects an empty batch and a heterogeneous batch`
- [ ] `it refreshes updated-at to now on every entity, not only when unset`
- [ ] `it throws when the batch mixes null and set auto-increment keys (different column sets)`
- [ ] `it returns the affected-row count from the query builder`

Notes: the query builder signature comes from the "Shared contract" in `_plan.md`. `applyInsertTimestamps()` only fills unset values, so without an explicit refresh, upserting a loaded entity writes a stale `updated_at`. The no-lifecycle-events and no-id-hydration behaviour must be in the method docblock.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
