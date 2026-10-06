# Task 004: Document the AdminSection requirements and new exception in admin.md

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update packages/docs-markdown/docs/packages/admin.md so it states that an admin section class must both carry `#[AdminSection]` and implement `AdminSectionInterface`, and that parsing a class missing either throws `AdminException` (missing attribute is reported first). Check the admin README stays a slim pointer.

Do NOT document `AdminSectionDiscovery::discoverInModule()` or discovery internals. Discovery is not wired into boot yet, and #314 will reshape it. Documenting it now would describe a feature that does not activate.

## Context
- Related files: packages/docs-markdown/docs/packages/admin.md (the "Registering an Admin Section" text around :18, and the `AdminException` note at :219), packages/admin/README.md, docs/DOCS-STANDARDS.md
- Today admin.md does not mention `AdminSectionDiscovery` at all; keep it that way.

## Requirements (Test Descriptions)
- [ ] `docs state a section class needs both #[AdminSection] and AdminSectionInterface`
- [ ] `docs list the missing-attribute and missing-interface AdminException cases alongside the existing registry cases`
- [ ] `README remains a slim pointer per DOCS-STANDARDS`

## Acceptance Criteria
- Docs accurate against the code
- No documentation of discoverInModule (out of scope, #314)

## Implementation Notes
Implemented with strict TDD (tests written first and observed failing). See the commit for the code.
