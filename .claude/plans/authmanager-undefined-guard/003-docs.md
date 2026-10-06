# Task 003: Document the new errors

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update `packages/docs-markdown/docs/packages/authentication.md` "Guards" and "Guard Drivers" sections to say an undefined guard name or a guard entry without `driver` throws `AuthException`. Mention in the authorization docs that the `#[Can]` boot check catches an undefined default guard name.

## Context
- Related files: `packages/docs-markdown/docs/packages/authentication.md`, `packages/docs-markdown/docs/packages/authorization.md`, `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] `Guards section states that an undefined guard name throws AuthException`
- [x] `Guard Drivers section states that a guard without a driver key throws AuthException`
- [x] `authorization docs boot check mentions an undefined guard name`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS
- Existing README/doc tests still pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
