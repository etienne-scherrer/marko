# Task 006: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document option syntax, `--`, declared flags, and positional/option ordering in `cli.md` and the command authoring section of `core.md`. Fix affected package docs pages (page-cache `--tag`).

## Context
- Related files: packages/docs-markdown/docs/packages/cli.md, core.md, page-cache.md, queue.md; docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs describe --name value, --name=value, -- and declared flags`
- [x] `docs show getOptionValues and positional-only getArguments`
- [x] `docs show page-cache:purge --tag <tag>`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
(Left blank - filled in by programmer during implementation)
