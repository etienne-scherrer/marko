# Task 007: Structure test over shipped config files

**Status**: completed
**Depends on**: 003, 004, 005
**Retry count**: 0

## Description
A repository-level test that scans every `packages/*/config/*.php` and fails on `(int)`/`(float)`/`(bool)` casts, `filter_var`, global `env()` calls, or raw `$_ENV`/`getenv` reads.

## Context
- Related files: tests/ConfigEnvReadsTest.php (new, monorepo root suite), tests/Fixtures/ConfigEnvReads/ (violating fixture)
- Must NOT live in packages/config/tests: packages are split into standalone repos, where `../../{other-package}` does not exist. Follow the precedent of tests/Psr7ContainmentTest.php.

## Requirements (Test Descriptions)
- [x] `it finds no casts or filter_var in shipped config files`
- [x] `it finds no global env() calls in shipped config files`
- [x] `it finds no raw $_ENV or getenv reads in shipped config files`
- [x] `it flags each banned pattern in a violating fixture`

## Acceptance Criteria
- Test passes against the migrated files and fails on the old pattern (proven by the fixture)

## Implementation Notes (from review)
- Prefer `token_get_all()` over regex so comments and strings are ignored. Match `T_INT_CAST`/`T_DOUBLE_CAST`/`T_BOOL_CAST`, `T_STRING` `filter_var`/`getenv`/`env` that is not preceded by `::`, `->` or `\\Name\\`, and `T_VARIABLE` `$_ENV`. Make sure `Env::bool(` and `getenv(` are not misreported as `env(`.
- Violation messages name file and line.
