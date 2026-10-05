# Task 007: Documentation

**Status**: completed
**Depends on**: 003, 004, 005, 006
**Retry count**: 0

## Description
Document casts, timestamps, encrypted columns and timezone handling in packages/docs-markdown/docs/packages/database.md, link from encryption.md, check the README pointer.

## Requirements (Test Descriptions)
- [x] `database.md documents custom casts, timestamps, encrypted columns and timezone handling`
- [x] `encryption.md links to the database encrypted-columns section`
- [x] `database.md tells users to declare type: 'datetime' (or 'timestamp') for timestamp columns, since DateTimeImmutable still infers varchar`
- [x] `database.md documents encrypted-column limitations: no querying by value, no unique/index, NULL stored unencrypted`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
(Left blank - filled in by programmer during implementation)
