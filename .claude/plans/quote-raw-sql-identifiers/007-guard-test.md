# Task 007: Guard test against hard-coded identifier quoting

**Status**: completed
**Depends on**: [001, 003, 004, 005, 006]
**Retry count**: 0

## Description
Token-scan packages/*/src (excluding database-mysql and database-pgsql) and fail on a string literal holding a backtick or double quote inside a statement that builds SQL.

## Context
- Related files: tests/SqlIdentifierQuotingTest.php, tests/Support/SqlIdentifierQuoting/, tests/Fixtures/SqlIdentifierQuoting/
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it finds no hard-coded identifier quoting in package sources`
- [ ] `it flags backtick and double-quote delimiters in a violating fixture`
- [ ] `it ignores quote characters in comments and in statements that build no SQL`
- [ ] `it excludes the database driver packages`
- [ ] `it flags delimiters inside interpolated double-quoted strings and heredocs`
- [ ] `it does not flag exception messages, markdown or HTML/CSS strings that contain backticks or double quotes`

## Detector rules (from review; the naive version fails on current sources)
- Use `PhpToken::tokenize()`. Skip `T_COMMENT`/`T_DOC_COMMENT`. Split into statements on `;`.
- A statement "builds SQL" only if one of its string tokens contains a **case-sensitive, whole-word, uppercase** SQL keyword: `SELECT`, `INSERT INTO`, `UPDATE`, `DELETE FROM`, `TRUNCATE`, `FROM`, `JOIN`, `WHERE`. Lowercase words in messages or CSS (`user-select`, "update the table") must not count.
- Inspect every string token kind: `T_CONSTANT_ENCAPSED_STRING`, `T_ENCAPSED_AND_WHITESPACE` (the parts of `"... \"$x\" ..."` and heredocs), and nowdoc bodies. Treat a backtick or an escaped/literal double quote **inside** the string content as a violation (not the string's own delimiters).
- Known non-SQL sites that must stay green (put them in the fixtures as negative cases): `packages/database/src/Query/IdentifierValidator.php:167` (`str_contains($expression, '`')`), `packages/docs-fts/src/FtsQueryBuilder.php:54` (`'"' . $term . '"'`), backtick-bearing exception messages (core, routing, admin, devai), `packages/errors-simple/src/Formatters/BasicHtmlFormatter.php` CSS/JS.
- Report file:line for each violation in the failure message (loud errors).
- Sanity check: on today's sources the only true positives are `TruncateDatabase.php:72` and `:135` (the other sites interpolate names bare, which this guard does not detect; tasks 001/004-006 cover them). After 003 lands the real-source test must pass with zero violations.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
