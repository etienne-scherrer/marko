# Task 005: #[Timestamps]

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
Entity-level `#[Timestamps(createdAt: 'createdAt', updatedAt: 'updatedAt')]`; Repository insert/insertBatch set both, update sets updatedAt. Time comes from a protected `now()` seam (UTC) until a clock (#182) lands.

## Context
- Depends on 004 (not 001) because both edit `EntityMetadataFactory::parse()`, `Repository::update()` and hydrator dirty checking.
- Metadata: trailing optional param on `EntityMetadata` (see _plan.md "Interface Contracts").
- "Explicitly set" = property is initialized AND non-null. Timestamp properties are commonly declared `public DateTimeImmutable $createdAt;` with no default; `extract()` calls `getValue()` and throws `Error` on uninitialized properties, so timestamps must be assigned before any extract.
- Apply timestamps inside `insert()`, `insertBatch()` and `update()` (i.e. after `EntityCreating`/`EntityUpdating` are dispatched, so listener changes count). In `insertBatch()` apply once per entity before the first `extractBatchRow()` call (rows are extracted twice).
- Update: bump `updatedAt` if the parent OR any participating companion is dirty; if the user already changed `updatedAt` (it is dirty), keep their value. Add it to `$data` via `toDatabaseValue()` before the empty-check short-circuit is evaluated against the real dirty set.

## Requirements (Test Descriptions)
- [x] `it sets createdAt and updatedAt on insert`
- [x] `it respects explicitly set timestamp values on insert`
- [x] `it sets only updatedAt on update`
- [x] `it does not touch updatedAt when nothing is dirty`
- [x] `it throws when a timestamp property is missing or not a DateTimeImmutable column`
- [x] `it sets timestamps on uninitialized non-nullable DateTimeImmutable properties`
- [x] `it sets timestamps for every entity in insertBatch`
- [x] `it bumps updatedAt when only a companion is dirty`
- [x] `it keeps a user-modified updatedAt on update`
- [x] `it throws when Timestamps is declared on an extender entity`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Added Attributes\Timestamps, EntityMetadata createdAtProperty/updatedAtProperty (also carried by withExtenders), EntityMetadataFactory::resolveTimestamps() validation, Repository::now() UTC seam, applyInsertTimestamps() for insert() and insertBatch(), and updatedAt bump in update(). Tests in packages/database/tests/Entity/Cast/TimestampsTest.php.
