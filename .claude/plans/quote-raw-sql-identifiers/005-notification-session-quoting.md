# Task 005: Notification and session fixed-name sites

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Quote the notifications table in DatabaseChannel and DatabaseNotificationRepository and the sessions table in DatabaseSessionHandler.

## Context
- Related files: packages/notification/src/Channel/DatabaseChannel.php, packages/notification-database/src/Repository/DatabaseNotificationRepository.php, packages/session-database/src/Handler/DatabaseSessionHandler.php
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it quotes the notifications table when sending`
- [ ] `it quotes the notifications table in every repository statement`
- [ ] `it quotes the sessions table in every statement`

## Notes (from review)
- DatabaseChannel has two INSERT sites (single send, line 49, and chunked batch, line 82); quote both. DatabaseSessionHandler has both the MySQL upsert and the PostgreSQL upsert; quote both.
- Existing tests that assert exact SQL text (e.g. DatabaseChannelBatchTest, session-database Unit/Feature tests) use `"`-quoting doubles; update those assertions to the quoted form. The real-database session suites (packages/session-database/tests/Integration) must still pass.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
