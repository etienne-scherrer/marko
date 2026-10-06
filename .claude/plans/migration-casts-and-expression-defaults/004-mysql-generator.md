# Task 004: MySQL Generator — Expression Defaults

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Emit expression defaults raw, wrapped in parentheses unless they are a `CURRENT_TIMESTAMP`-family keyword (which MySQL accepts bare) or already parenthesized. Replace the prefix-matching allow-list with the shared shortcut rule, keep `'NULL'`, and quote `Literal`.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php, packages/database-mysql/tests/Sql/MySqlGeneratorTest.php

## Requirements (Test Descriptions)
- [x] `it emits a parenthesized UUID() expression default`
- [x] `it emits CURRENT_TIMESTAMP(6) unquoted and unwrapped`
- [x] `it wraps a function call shortcut default in parentheses`
- [x] `it quotes a literal default that looks like a function`
- [x] `it quotes a string that merely starts with a keyword`
- [x] `it keeps an expression default when the type changes to one that cannot hold a literal`

## Acceptance Criteria
- All requirements have passing tests
