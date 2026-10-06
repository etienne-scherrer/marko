# Task 001: TokenConfig reads and validates token_expiration_days

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\AuthenticationToken\Config\TokenConfig` with `expirationDays(): ?int`, reading `authentication-token.token_expiration_days`. Accept a positive int or `null`; throw a `ConfigException` with a helpful message for anything else.

## Context
- Related files: `packages/authentication-token/config/authentication-token.php`, new `packages/authentication-token/src/Config/TokenConfig.php`, new `packages/authentication-token/tests/Config/TokenConfigTest.php`
- Patterns to follow: `packages/authorization/src/Config/AuthorizationConfig.php` and its test (shipped-config helper using `ConfigRepository`)

## Requirements (Test Descriptions)
- [x] `it returns 365 days for the shipped config`
- [x] `it returns the configured number of days`
- [x] `it returns null when token_expiration_days is null so tokens never expire by default`
- [x] `it throws a ConfigException when token_expiration_days is zero or negative`
- [x] `it throws a ConfigException when token_expiration_days is not an integer`
- [x] `it throws ConfigNotFoundException when token_expiration_days is missing`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
`TokenConfig::expirationDays()` reads via `get()` (not `getInt()`, which would coerce null/strings) and accepts only a real int >= 1 or null. Numeric strings such as an unconverted env value are rejected loudly, matching the issue's "int >= 1 or null; anything else throws"; cast env values with `(int)` in the config file.
