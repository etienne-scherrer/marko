# Task 004: Router binds JSON body fields

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Switch `Router::resolveParameters()` body lookup to `Request::input()` so JSON fields bind to typed controller parameters, keeping route > body > query > default priority, and answer 400 when a malformed JSON body is hit during binding.

## Context
- Related files: `packages/routing/src/Router.php`, `packages/routing/tests/RouterTest.php`

## Requirements (Test Descriptions)
- [x] `it binds json body fields to typed controller parameters`
- [x] `it prefers a json body value over a query value of the same name`
- [x] `it returns 400 when a json request body is malformed during parameter binding`

## Acceptance Criteria
- All requirements have passing tests; existing Router tests unchanged

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
