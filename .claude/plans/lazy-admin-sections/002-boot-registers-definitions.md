# Task 002: Boot Registers Definitions Only

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Change marko/admin's boot callback to hand each discovered `AdminSectionDefinition` to `registerDefinition()` and never call `$container->get()`. Update wiring tests.

## Context
- Related files: packages/admin/module.php, packages/admin/tests/Unit/ModuleWiringTest.php

## Requirements (Test Descriptions)
- [x] `it builds no section at boot`
- [x] `it boots when a section constructor throws`
- [x] `it builds an attribute-declared section on first use with no manual registration`
- [x] `it still fails boot for a missing class or a class without AdminSectionInterface`
- [x] `it throws sectionIdMismatch on first use, not at boot`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Rewrite the existing ModuleWiringTest test "throws sectionIdMismatch when getId differs from the attribute id". It currently expects the throw at boot. Now boot must succeed and the first `get()`/`all()` must throw.
- The existing "registers an attribute-declared section at boot..." test should now assert the section is built on first `all()`, not at boot.
- Remove the unused imports from module.php (`AdminSectionInterface`, `AdminException`, `ContainerInterface`) once validation moves into the registry.
