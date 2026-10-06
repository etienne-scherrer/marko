# Task 004: RoleRepository slug validation

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
save() and isSlugUnique() reject slugs outside the pattern; findBySlug() returns null for them without querying.

## Context
- Related files: packages/admin-auth/src/Repository/RoleRepository.php
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories

## Requirements (Test Descriptions)
- [x] `it throws invalidRoleSlug when saving a role with a non-canonical slug`
- [x] `it saves a role with a canonical slug`
- [x] `it throws invalidRoleSlug from isSlugUnique for a non-canonical slug`
- [x] `it returns null from findBySlug for a non-canonical slug without querying`
- [x] `it throws invalidRoleSlug from insertBatch and inserts nothing when any slug is non-canonical`

Note: `insertBatch()` is inherited from `Repository` and issues a multi-row INSERT without calling `save()`. Override it to validate every Role before delegating.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
