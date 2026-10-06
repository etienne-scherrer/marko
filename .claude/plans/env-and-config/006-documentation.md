# Task 006: Documentation updates

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document env mirroring (env.md), the shared config repository (config.md), `AppEnvironment` and its accepted values (core.md), and the errors-simple environment detection change (errors-simple.md).

## Context
- Related files: packages/docs-markdown/docs/packages/{env,config,core,errors-simple}.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `env.md documents that real environment variables are mirrored into $_ENV`
- [x] `config.md notes the repository is shared`
- [x] `core.md documents AppEnvironment and the accepted values`
- [x] `core.md discovery-cache section (around line 275) is updated: the gate uses AppEnvironment::isDevelopment() (local/dev/development skip the cache), honours MARKO_ENV, and falls back to getenv`
- [x] `errors-simple.md documents that an unset environment is now production and that detection is delegated to AppEnvironment`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
