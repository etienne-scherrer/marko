# Task 002: Document the allowlist in the testing docs

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Rewrite the "Test environment" section of the `marko/testing` docs page so it states that the destructive helpers run only when `APP_ENV` is `testing` or `test`, distinguishes this from the non-destructive migrate (allowed outside production), and links to the database docs' destructive-command policy.

## Context
- Related files: `packages/docs-markdown/docs/packages/testing.md` ("Test environment" section), `packages/docs-markdown/docs/packages/database.md` ("Environment Behaviour" section, anchor `#environment-behaviour`), `packages/testing/README.md` (check it does not repeat the old policy)
- Patterns to follow: `docs/DOCS-STANDARDS.md`
- #255 edits a different section of testing.md; keep this edit confined to "Test environment".

## Requirements (Test Descriptions)
- [x] `it states that migrations run in any environment except production`
- [x] `it states that TruncateDatabase::truncate() and fresh: true run only in testing and test`
- [x] `it links to the database docs' destructive-command policy`
- [x] `it explains that the test helpers are stricter than the db:* commands` (development is refused too and there is no `--force` equivalent, so a reader following the link is not confused by the CLI's development/`--force` rules)
- [x] `it notes that MARKO_ENV, when set, takes precedence over APP_ENV`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS
- Only the "Test environment" section of testing.md changes

## Implementation Notes
- Rewrote the last paragraph of "Test environment" and added a paragraph linking to `/docs/packages/database/#environment-behaviour`.
- Added one-sentence pointers back to "Test environment" in the "Fresh schema" and `TruncateDatabase` paragraphs.
- The package README previously said only that the helpers refuse production. Updated it to mention the testing-only rule.
