# Task 006: End-to-end pipeline and event tests

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Prove the exit criteria through a booted container and the real `MiddlewarePipeline`.

## Context
- Related files: packages/authentication/tests/Integration/RememberMeIntegrationTest.php

## Requirements (Test Descriptions)
- [ ] `it emits a remember cookie with configured attributes on login with remember`
- [ ] `it authenticates a new request carrying the remember cookie and no session`
- [ ] `it emits an expired remember cookie and clears the stored token on logout`
- [ ] `it delivers LoginEvent, LogoutEvent and FailedLoginEvent to registered observers`

## Acceptance Criteria
- All requirements have passing tests
