# Task 007: Docs: admin-auth.md and database.md

**Status**: completed
**Depends on**: [001, 002, 003, 004, 005]
**Retry count**: 0

## Description
Document the key/slug format, email normalization, sync repair and upgrade note in admin-auth.md; add a collation note to database.md.

## Context
- Related files: packages/docs-markdown/docs/packages/admin-auth.md, packages/docs-markdown/docs/packages/database.md
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories

## Requirements (Test Descriptions)
- [x] `it documents the permission key and role slug format`
- [x] `it documents email normalization and the upgrade note`
- [x] `it documents that string equality follows the server collation`
- [x] Upgrade note shows a query that finds emails that collide once lowercased (`SELECT LOWER(email) ... GROUP BY LOWER(email) HAVING COUNT(*) > 1`) and must be resolved before `UPDATE admin_users SET email = LOWER(email)`. Without that step the UPDATE fails on PostgreSQL's unique index.
- [x] Upgrade note explains that existing roles with non-canonical slugs (e.g. `Editor`) will throw on their next save, and shows how to find and rename them. It also says that stored case variants of registered permission keys are repaired by `admin-auth:permissions:sync`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
