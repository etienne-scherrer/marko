# Task 005: TokenManager / TokenGuard expiry in the database zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`TokenManager` formats the caller's expiresAt in the caller's own zone and `TokenGuard` re-parses it in the PHP default zone, so a UTC expiry on a New York server lives 4-5 hours too long. Inject `DatabaseTimezoneConfig` into both (and `TokenGuardFactory`) and convert on write and read.

## Context
- Related files: packages/authentication-token/src/Service/TokenManager.php, src/Guard/TokenGuard.php, src/Guard/TokenGuardFactory.php, tests/Service, tests/Guard, tests/Feature/TokenGuardWiringTest.php
- Keep `PersonalAccessToken::$expiresAt` as `?string` (changing the type would change the entity-derived column type).
- Constructors: insert `DatabaseTimezoneConfig $databaseTimezoneConfig` immediately after `ClockInterface $clock` in `TokenManager`, `TokenGuard` and `TokenGuardFactory`. The `TokenGuard` positional order changes (docs testing.md:184 is updated in task 008).
- `TokenGuard::lookUpToken()` replaces `new DateTimeImmutable($token->expiresAt)` with `$this->databaseTimezoneConfig->parse(...)`.
- tests/Feature/TokenGuardWiringTest.php builds a bare `new Container()` with no `ProjectPaths`, so autowiring `DatabaseTimezoneConfig` fails. Bind `DatabaseTimezoneConfig::fromName('UTC')` as an instance in its boot helper.

## Requirements (Test Descriptions)
- [x] `it stores an explicit expiry in the database timezone whatever the caller timezone`
- [x] `it stores the default expiry and created_at in the database timezone`
- [x] `it rejects a token whose stored expiry has passed when the PHP default timezone is not the database timezone`
- [x] `it accepts a token whose stored expiry is still ahead when the PHP default timezone is not the database timezone`
- [x] `it builds the token guard with the database timezone config`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
TokenManager, TokenGuard and TokenGuardFactory take `DatabaseTimezoneConfig` after the clock. Entity properties stay `?string`. Wiring test proves the container-built guard reads expiry in a Tokyo database zone.
