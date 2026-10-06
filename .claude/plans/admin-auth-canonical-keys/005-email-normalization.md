# Task 005: AdminUserRepository email normalization

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Lowercase admin emails on save and in findByEmail so every driver agrees.

## Context
- Related files: packages/admin-auth/src/Repository/AdminUserRepository.php
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories

## Requirements (Test Descriptions)
- [x] `it lowercases the email when saving an admin user`
- [x] `it lowercases multibyte characters in the email`
- [x] `it looks up findByEmail with the lowercased email`
- [x] `it lowercases emails in insertBatch`

Note: `insertBatch()` is inherited from `Repository` and bypasses `save()`. Override it to lowercase each AdminUser's email before delegating.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
