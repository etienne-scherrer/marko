# Task 004: SessionGuard remember hardening

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Stop `SessionGuard` silently ignoring `remember: true`, make the remember lookup compatible with providers that compare stored hashes, and take the cookie lifetime and prefix from configuration.

## Context
- Related files: packages/authentication/src/Guard/SessionGuard.php, src/Token/RememberTokenManager.php, packages/admin-auth/src/AdminUserProvider.php

## Requirements (Test Descriptions)
- [ ] `it throws AuthException when remember is requested without a cookie jar or token manager`
- [ ] `it throws AuthException when the user provider does not store the remember token`
- [ ] `it looks up remember users by the hashed token`
- [ ] `it authenticates via remember cookie with FakeUserProvider`
- [ ] `it sets the remember cookie for the token manager lifetime`
- [ ] `it names the remember cookie with the configured prefix`
- [ ] `AdminUserProvider matches remember tokens with a constant-time comparison`

## Acceptance Criteria
- All requirements have passing tests
