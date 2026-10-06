# Task 007: Package docs and READMEs

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Fix the docs: queue-database "Running Migrations" uses `marko db:migrate` and lists the tables; session-database "Creating the Sessions Table" replaces the raw SQL with `marko db:migrate`; database.md's "Tables no entity owns (sessions, jobs, ...)" example list updated. READMEs stay slim pointers.

Also fix these:
- **Describe the real flow.** `marko db:migrate` creates the tables only in development (or with `--generate`), where it writes a migration. Commit that migration and deploy it. In production, `db:migrate` only warns about drift.
- **Stale queue-database.md copy.** Line 6 says "Includes migrations for both tables" and line 32 says "the bundled migration creates". Both must say the package ships entities.
- **Upgrade note.** `CreateJobsTable`/`CreateFailedJobsTable` are removed. An app migration that does `return new CreateJobsTable();` must be replaced with inline DDL, or deleted if the tables already exist.

## Context
- Related files: packages/docs-markdown/docs/packages/{queue-database,session-database,database}.md, packages/{queue,session}-database/README.md
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [ ] `it documents marko db:migrate instead of marko migrate`
- [ ] `it documents no raw CREATE TABLE sessions SQL`

## Acceptance Criteria
- Docs tests (docs-markdown, readme checks) pass

## Implementation Notes
