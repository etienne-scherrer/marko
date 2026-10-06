# Task 001: Quote identifiers in DatabaseSearchDriver

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Quote the table, every searchable field, every filter field and the sort column through ConnectionInterface::quoteIdentifier(). Keep the identifier regex (it still rejects dotted and odd names before they reach SQL) and the sort-direction allowlist. Add the missing marko/database require to marko/search.

## Context
- Related files: packages/search/src/Driver/DatabaseSearchDriver.php, packages/search/tests/Driver/DatabaseSearchDriverTest.php, packages/search/composer.json
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it quotes the table and searchable fields through the connection`
- [ ] `it quotes filter fields through the connection for every operator`
- [ ] `it quotes the sort column through the connection and keeps the direction bare`
- [ ] `it quotes reserved-word identifiers instead of interpolating them bare`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
