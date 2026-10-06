# Task 003: Rewrite env docs around the .env loader

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Describe `marko/env` as the `.env` loader (`EnvLoader`) in `env.md` and the package README, move `env()` into a "Deprecated" section mapping each coercion (`true`/`false`/`null`/`empty`) to its `Env::*` equivalent, document the `function_exists` shadowing risk, and link that section from `config.md` "Environment Variables" with a small edit (#343 edits the same page).

## Context
- Related files: `packages/docs-markdown/docs/packages/env.md`, `packages/env/README.md`, `packages/docs-markdown/docs/packages/config.md`, `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] env.md front matter and intro describe the `.env` loader only
- [x] env.md has a Deprecated section with the coercion → `Env::*` mapping and 1.0 removal
- [x] env.md explains the shadowing risk of the `function_exists` guard
- [x] README is a slim pointer without `env()` in the quick example
- [x] config.md links the Deprecated section

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
Docs-only. Quote the canonical deprecation message from `_plan.md` Architecture Notes exactly as task 001 implements it.
