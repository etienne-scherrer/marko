# Task 001: Expression and Literal Value Objects

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Database\Schema\Expression` (a raw SQL default) and `Marko\Database\Schema\Literal` (a string default that is always quoted), plus the shortcut rule that decides when a plain string default is an expression.

## Context
- Related files: packages/database/src/Schema/Expression.php, packages/database/src/Schema/Literal.php, packages/database/src/Exceptions/MigrationException.php
- Patterns to follow: readonly value objects in packages/database/src/Schema

## Requirements (Test Descriptions)
- [x] `it holds the raw SQL of an expression default`
- [x] `it rejects an empty expression loudly`
- [x] `it recognizes bare timestamp keywords and their precision forms as shortcuts`
- [x] `it recognizes a zero-argument function call as a shortcut`
- [x] `it does not treat ordinary strings or function calls with arguments as shortcuts`
- [x] `it compares expressions ignoring case, whitespace and redundant outer parentheses`
- [x] `it holds the value of a literal default`
- [x] `it is usable as a Column attribute default`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Expression::isShortcut() matches `CURRENT_TIMESTAMP|CURRENT_DATE|CURRENT_TIME|LOCALTIMESTAMP|LOCALTIME` with optional `(n)` and `/^[a-z_][a-z0-9_]*\(\)$/i`. Empty expressions throw `MigrationException::emptyDefaultExpression()`.
