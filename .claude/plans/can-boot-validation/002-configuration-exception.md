# Task 002: AuthorizationConfigurationException

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Dedicated MarkoException (not an HTTP exception) thrown when #[Can] routes exist but the guard or Gate cannot be built.

## Context
- Related files: packages/authorization/src/Exceptions/
- Patterns to follow: existing authorization package classes; DiscoveryCacheContributorInterface implementations

## Requirements (Test Descriptions)
- [x] `it names the guard in the message`
- [x] `it lists the Can routes as examples, capped at five with a count of the rest`
- [x] `it keeps the original exception as previous`
- [x] `it suggests configuring authentication.guards, a user provider and a session driver`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Message names the guard and the underlying error; context lists up to five routes; suggestion covers session, token and #[WithoutMiddleware].
