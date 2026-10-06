# Task 001: MySqlServerVersion value object

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
A readonly value object that parses the string `SELECT VERSION()` (or `PDO::ATTR_SERVER_VERSION`) reports into a server type and a normalized version number, with a version comparison helper for later capability checks (#325).

## Context
- Related files: packages/database-mysql/src/Connection/MySqlServerVersion.php (new), packages/database-mysql/src/Exceptions/ (new exception)
- Patterns to follow: MarkoException static factories (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it parses a MySQL version string`
- [x] `it parses a MariaDB 11 version string with a distribution suffix`
- [x] `it strips the 5.5.5- prefix MariaDB before 11.0 reports over the protocol`
- [x] `it compares the version with isAtLeast`
- [x] `it throws when the version string has no version number`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- `packages/database-mysql/src/Connection/MySqlServerVersion.php` and `src/Exceptions/ServerVersionException.php` (`unreadable()`) already exist in the worktree. Check them against the requirements above and add the missing tests. Do not recreate or rename them.
