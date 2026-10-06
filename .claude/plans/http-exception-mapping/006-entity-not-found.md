# Task 006: EntityNotFoundException maps to 404

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
RepositoryException::entityNotFound() returns a new EntityNotFoundException subclass that renders 404 without leaking the entity class or ID.

## Context
- Related files: packages/database/src/Exceptions/EntityNotFoundException.php, packages/database/src/Exceptions/RepositoryException.php

## Requirements (Test Descriptions)
- [x] `it returns EntityNotFoundException from RepositoryException::entityNotFound`
- [x] `it remains catchable as RepositoryException`
- [x] `it renders 404 without leaking the entity class or ID`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

