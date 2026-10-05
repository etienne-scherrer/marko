# Task 001: Remember cookie config and AuthConfig getters

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Restructure `authentication.remember` config to hold the lifetime and cookie attributes, and expose typed getters on `AuthConfig`. Declare the package's real composer dependencies.

## Context
- Related files: packages/authentication/config/authentication.php, src/Config/AuthConfig.php, composer.json
- Patterns to follow: `SessionConfig` getters

## Requirements (Test Descriptions)
- [ ] `it returns remember lifetime in minutes from config`
- [ ] `it returns remember cookie prefix from config`
- [ ] `it returns remember cookie path, domain, http only and same site from config`
- [ ] `it returns null remember cookie domain when configured as empty string`
- [ ] `it follows session cookie secure flag when remember cookie secure is null`
- [ ] `it uses explicit remember cookie secure flag when configured`
- [ ] `it requires marko/config, marko/session and marko/routing`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Config keys: `remember.lifetime`, `remember.cookie.{prefix,path,domain,secure,http_only,same_site}`.
