# Task 006: Docs and changelog

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document the environment policy in one command x environment table in database.md (including `db:migrate`'s generation rule), document `isTesting()` in core.md, and add a changelog upgrade note that staging CI scripts that rebuild, reset, roll back or seed need `--force`.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md (`## CLI Commands`, `### Environment Behaviour`, workflow snippets), packages/docs-markdown/docs/packages/core.md (`### Application Environment`), CHANGELOG.md, docs/DOCS-STANDARDS.md
- Also stale and must be updated: database.md CLI table (lines ~1389-1392, "(refused in production)"), packages/docs-markdown/docs/guides/database.md (~line 97, "refuse to run in production"), core.md API reference listing (~line 561, add `isTesting(): bool`) and the core.md environment table (~line 161)
- Use the effect wording from task 002 if quoting messages; document that `--force --no-interaction` is the scripted (CI) form, since `--force` alone still prompts when a terminal is attached
- READMEs stay slim pointers; no README change needed unless one mentions the old policy

## Requirements (Test Descriptions)
- [x] `database.md has one command x environment policy table covering db:migrate generation and the four destructive commands`
- [x] `database.md documents --force and the confirmation for destructive commands`
- [x] `core.md lists isTesting() with testing and test`
- [x] `CHANGELOG.md has an Unreleased upgrade note about --force for staging CI`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS.md

## Implementation Notes
