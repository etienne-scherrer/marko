# Task 003: Unmanaged / ignored indexes in DiffCalculator

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Let developers opt hand-made indexes out of the entity diff: per entity via `#[Table(unmanagedIndexes: [...])]` and project-wide via `config/database.php` `migrations.ignore_indexes` (names or glob patterns). The diff never drops them.

## Context
- Related files: Attributes/Table.php, Entity/EntityMetadata.php, Entity/EntityMetadataFactory.php, Entity/SchemaBuilder.php, Schema/Table.php, Schema/SchemaRegistry.php, Diff/DiffCalculator.php, Config/DatabaseConfig.php, module.php

## Requirements (Test Descriptions)
- [x] `it does not drop an index listed in the table unmanagedIndexes`
- [x] `it does not drop an index matching a configured ignore pattern`
- [x] `it still drops undeclared indexes that are not ignored`
- [x] `it merges unmanagedIndexes declared on an extender into the parent table`
- [x] `it reads migrations.ignore_indexes from the database config`
- [x] `it rejects a non-string entry in migrations.ignore_indexes`
- [x] `it builds DiffCalculator from module.php with the configured ignore list`
- [x] `it keeps unmanagedIndexes through withColumn, withIndex and withForeignKey`
- [x] `it reads migrations.ignore_indexes in DatabaseConfig::fromArray`
- [x] `it defaults ignoreIndexes to an empty list when migrations is absent`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Depends on 001 only to serialize edits to module.php's `bindings` array (001 rewrites the SeederRunner closure).
- `Schema\Table::withColumn/withIndex/withForeignKey` each call `new self(...)`; every one must pass
  `unmanagedIndexes` through, otherwise SchemaRegistry's extender merge silently drops it.
- `DatabaseConfig` has two construction paths: the constructor and `fromArray()` (reflection over a `$props` list).
  Add `ignoreIndexes` to both and validate in `validateConfigArray()` (must be a list of strings; throw
  ConfigurationException otherwise).
- `DiffCalculator::__construct(array $ignoredIndexes = [])` keeps `new DiffCalculator()` call sites working; the
  module.php closure resolves `DatabaseConfig` from the container. DiffCommand gets the same behaviour automatically.
- Ignored/unmanaged names only suppress drops (`findIndexesToDrop`); they never suppress adds.
