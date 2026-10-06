# Task 005: Docs

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Update docs so they state that every framework 401 for a stateless guard carries the guard's challenge, including `#[Can]`.

## Context
- Related files: packages/docs-markdown/docs/packages/authorization.md (failure table ~line 255), authentication-token.md (401 paragraph ~line 148), authentication.md ("Stateless guards" ~line 226 and AuthMiddleware section ~line 466), admin-auth.md (JSON 401 ~line 44), packages/docs-markdown/docs/guides/authentication.md (lines ~114 and ~223 credit only `AuthMiddleware` with the challenge); READMEs only if they mention the 401 shape.

## Requirements (Test Descriptions)
- [x] authorization.md failure table: the `#[Can]` guest row's Exception column names `Marko\Authentication\Exceptions\UnauthenticatedException` (not `HttpException`), and the text says the 401 carries a stateless guard's WWW-Authenticate challenge
- [x] authentication-token.md says `#[Can]` sends `WWW-Authenticate: Bearer` too
- [x] authentication.md "Stateless guards" says every framework 401 carries the challenge and documents UnauthenticatedException::forGuard(). The AuthMiddleware section names UnauthenticatedException as the thrown type
- [x] admin-auth.md: a JSON guest gets an UnauthenticatedException, with the challenge when the admin guard is stateless
- [x] guides/authentication.md: lines ~114 and ~223 say AuthMiddleware AND `#[Can]` (every framework 401) send the challenge
- [x] existing docs/README tests still pass

## Acceptance Criteria
- Docs accurate to implementation

## Implementation Notes
