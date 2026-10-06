# Task 002: Partial index `where` in core schema model

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add an optional `where` predicate to `#[Index]`, `IndexMetadata` and `Schema\Index`, and carry it through `SchemaBuilder` and `SchemaRegistry` (including extender-merged indexes).

## Context
- Related files: packages/database/src/Attributes/Index.php, Entity/IndexMetadata.php, Entity/EntityMetadataFactory.php, Entity/SchemaBuilder.php, Schema/Index.php, Schema/SchemaRegistry.php

## Requirements (Test Descriptions)
- [x] `it accepts a where predicate on the Index attribute`
- [x] `it parses the where predicate into IndexMetadata`
- [x] `it builds a schema Index carrying the where predicate`
- [x] `it treats indexes with different where predicates as not equal`
- [x] `it keeps the where predicate on indexes merged from extenders`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
