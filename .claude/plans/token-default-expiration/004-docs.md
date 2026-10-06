# Task 004: Docs page and README

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Update `packages/docs-markdown/docs/packages/authentication-token.md`: the Configuration section describes the default lifetime, validation and how to opt out (`null`); the `expiresAt` paragraph explains default vs explicit vs never; the Events table notes `expiresAt` is the computed expiry; the Database table notes `created_at` is set by `TokenManager`; the API reference lists the new `TokenManager` constructor and `TokenConfig`; the removed exceptions disappear from the API reference. Check the README (slim pointer per `docs/DOCS-STANDARDS.md`) and the config file comment.

## Context
- Related files: `packages/docs-markdown/docs/packages/authentication-token.md`, `packages/authentication-token/README.md`, `packages/authentication-token/config/authentication-token.php`
- Patterns to follow: `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] `it documents the default token lifetime and the null opt-out in the Configuration section`
- [x] `it documents the TokenManager constructor with TokenConfig and ClockInterface in the API reference`
- [x] `it no longer documents ExpiredTokenException or InvalidTokenException`

## Acceptance Criteria
- Docs accurate against the code
- README remains a slim pointer

## Implementation Notes
Updated the docs page (Configuration, expiresAt paragraph with a behaviour-change note, Events, Database, API reference with the new TokenManager constructor and TokenConfig, Exceptions), the config file comment, clock.md consumer line and the README quick example comment.
