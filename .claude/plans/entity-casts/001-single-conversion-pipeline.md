# Task 001: Single Conversion Pipeline Refactor

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Route every PHP <-> database value conversion through `EntityHydrator`. Add public `toDatabaseValue()` / `toPhpValue()` and delete `Repository::convertToDbValue()` so inserts and updates cannot disagree.

## Context
- Related files: packages/database/src/Entity/EntityHydrator.php, packages/database/src/Repository/Repository.php
- Test: packages/database/tests/Entity/ConversionPipelineTest.php
- Signatures (see _plan.md "Interface Contracts"): `public function toDatabaseValue(mixed $value, PropertyMetadata $meta): mixed` and `public function toPhpValue(mixed $value, PropertyMetadata $meta): mixed`; null short-circuits.
- `Repository::update()` currently converts via the column map only; it must look up `PropertyMetadata` from `$this->metadata->properties[$propertyName]` (parent) and `$companionMetadata->properties[$propertyName]` (companions) to call `toDatabaseValue()`.
- The extender branch of `hydrate()` must also call `toPhpValue()` (it has its own conversion call today).

## Requirements (Test Descriptions)
- [x] `it produces identical database values on insert and update for every built-in type`
- [x] `it json-encodes array columns on update`
- [x] `it exposes toDatabaseValue and toPhpValue on the hydrator`
- [x] `it no longer declares a private convertToDbValue on Repository`
- [x] `it converts dirty companion values through the hydrator on update`

## Acceptance Criteria
- All requirements have passing tests
- Existing database tests still pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
