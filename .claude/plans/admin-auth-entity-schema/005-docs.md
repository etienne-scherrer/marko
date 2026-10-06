# Task 005: Docs page and README

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
`admin-auth.md` Installation says to run `marko db:migrate`, lists the five tables from the entities, and notes the schema change for hand-created tables. README stays a slim pointer.

## Context
- Related files: packages/docs-markdown/docs/packages/admin-auth.md, packages/admin-auth/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs installation tells the user to run db:migrate and lists the five tables`
- [x] `docs note the schema change for tables created from the old SQL`

## Acceptance Criteria
- Follows DOCS-STANDARDS

## Implementation Notes
- Task 002 deletes `database/migrations/`; read the old SQL with `git show develop:packages/admin-auth/database/migrations/<file>`.
- Known `admin_user_roles` differences vs the entity: old table had no `id` column, `INT UNSIGNED` user_id/role_id, an extra `idx_admin_user_roles_role_id`, and MySQL-auto-named FKs. On such tables db:migrate proposes destructive changes and refuses non-interactively without `--force` (or `#[Table(unmanagedIndexes: [...])]` / `database.migrations.ignore_indexes` for kept indexes). Derive the note from the actual entity-vs-old-SQL difference; do not overclaim.
- Also glance at `packages/docs-markdown/docs/tutorials/build-an-admin-panel.md` (manual `INSERT INTO admin_user_roles`) for consistency.

Probed the upgrade path on MySQL (old SQL applied, then db:migrate): the MySQL generator cannot add an auto-increment PK column to an existing table (error 1075), so the docs tell users to add the two pivot id columns by hand first; after that db:migrate converges with no destructive changes.
