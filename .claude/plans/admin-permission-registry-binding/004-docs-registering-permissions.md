# Task 004: Docs: Registering Permissions singleton note

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update `packages/docs-markdown/docs/packages/admin-auth.md` "Registering Permissions" to say `marko/admin-auth` binds the registry as a shared singleton and that it can be replaced with a `#[Preference]`. Confirm README stays a slim pointer per DOCS-STANDARDS.

## Context
- Related files: packages/docs-markdown/docs/packages/admin-auth.md, packages/admin-auth/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] Docs state the registry is bound as a shared singleton by `marko/admin-auth`
- [x] Docs show replacing the registry via `#[Preference(replaces: PermissionRegistryInterface::class)]` on a class implementing the interface (the replacement stays shared)

## Gotchas
- Do NOT document `#[Preference(replaces: PermissionRegistry::class)]`. `Container::resolve()` checks preferences only for the requested id (the interface). After following the interface binding to `PermissionRegistry`, it does not check preferences again, so a Preference on the concrete class is silently ignored for every consumer that injects the interface.
- [x] README remains a slim pointer (no change needed unless non-compliant)

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
Implemented as specified.
