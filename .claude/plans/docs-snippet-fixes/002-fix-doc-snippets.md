# Task 002: Fix Wrong Snippets in READMEs and Docs

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Fix items 1-5 of #183: skeleton README controller, database driver README config layout, pgsql `schema` key, mail-log container namespace, and the stale `rate-limiting` name in `.claude/project-overview.md`. Move fictional examples to non-Marko namespaces.

## Context
- Related files: packages/skeleton/README.md, packages/database-pgsql/README.md, packages/database-mysql/README.md, packages/docs-markdown/docs/packages/{database-pgsql,database-mysql,mail-log}.md, packages/docs-markdown/docs/tutorials/custom-module.md, packages/docs-markdown/docs/concepts/plugins.md, .claude/project-overview.md
- Patterns to follow: packages/docs-markdown/docs/getting-started/first-application.md

## Requirements (Test Descriptions)
- [x] `it resolves every Marko class referenced in README and docs PHP snippets` (task 001 guard, red before these fixes)
- [x] Skeleton README example uses `Marko\Routing\Http\Response` and `#[Get('/')]`
- [x] Driver READMEs show the flat layout including `port`, with a `marko/database-readwrite` note
- [x] No config snippet for the database drivers shows a key `DatabaseConfig` does not read

## Acceptance Criteria
- Guard test passes
- `composer ci` green

## Implementation Notes
The mail-log conditional-binding snippet called `bind()`, which `ContainerInterface` does not declare; it is now a closure binding that returns `LogMailer` in development and `SmtpMailerFactory::create()` otherwise.
