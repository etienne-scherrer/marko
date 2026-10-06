# Task 005: AuthManager and module wiring

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Inject the event dispatcher and cookie jar into `AuthManager`, build a configured `RememberTokenManager`, pass all collaborators to `SessionGuard`, bind `CookieJarInterface` as a singleton and register the middleware.

## Context
- Related files: packages/authentication/src/AuthManager.php, module.php

## Requirements (Test Descriptions)
- [ ] `it resolves GuardInterface from a booted container as a SessionGuard with all collaborators`
- [ ] `it binds CookieJarInterface to a shared RequestCookieJar`
- [ ] `it registers QueuedCookiesMiddleware as global middleware`
- [ ] `it loads after the session driver modules`
- [ ] `it builds the token manager with the configured remember lifetime`

## Acceptance Criteria
- All requirements have passing tests
