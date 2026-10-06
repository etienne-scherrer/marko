# Task 008: Docs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 007
**Retry count**: 0

## Description
Document `Env` in config.md "Environment Variables" (accepted forms, empty = unset, errors); point env.md to it; update every package docs page that reproduces a config file; update `.claude/architecture.md` examples; authentication-token docs show `Env::int(..., min: 1)`; webhook docs list the new range checks; core docs describe strict `DISCOVERY_CACHE_ENABLED`.

## Requirements (Test Descriptions)
- [x] `docs show Env::* instead of casts on $_ENV`

## Acceptance Criteria
- READMEs stay slim pointers; docs pages accurate

## Implementation Notes
