# Task 002: Update Docs and Guidance for the Removal

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Turn the `env.md` deprecation section into a "Removed in 0.9.0" upgrade note and update every other doc and agent-guidance file that calls `env()` deprecated.

## Context
- Related files: `packages/docs-markdown/docs/packages/env.md`, `packages/docs-markdown/docs/packages/config.md`, `packages/env/README.md`, `.claude/architecture.md`, `.claude/code-standards.md`, `.claude/pr-review-process.md`
- Follow `docs/DOCS-STANDARDS.md`; README stays a slim pointer.

## Requirements (Test Descriptions)
- [x] `env.md` has a "Removed in 0.9.0: the env() Helper" section giving the exact `Call to undefined function env()` error
- [x] `env.md` keeps the before/after example and the `env()` → `Env::*` migration table
- [x] `env.md` no longer has the deprecated caution block or the `### env()` API entry
- [x] `env.md` replaces the `function_exists` shadowing paragraph, which describes a guard that no longer exists. The new text says that if another installed library (e.g. `illuminate/support`) defines a global `env()`, leftover calls do NOT fatal and silently run that library's `env()` with different coercion, so readers should search their config files for `env(` rather than rely on a boot error
- [x] No `@@NEW@@` (or other placeholder) text remains in `env.md`
- [x] `config.md` links to the new anchor and says the helper was removed in 0.9.0
- [x] README, `architecture.md`, `code-standards.md` and `pr-review-process.md` say the helper no longer exists

## Acceptance Criteria
- No doc still says `env()` is deprecated or will be removed in 1.0
- Docs follow DOCS-STANDARDS

## Implementation Notes
env.md: deprecated caution and ### env() API entry replaced by "Removed in 0.9.0: the `env()` Helper" (caution with exact error, stay-on-0.8.x path), before/after example and migration table kept, shadowing paragraph rewritten for the library-defined env() case. config.md links to #removed-in-090-the-env-helper. README, architecture.md, code-standards.md, pr-review-process.md reworded.
