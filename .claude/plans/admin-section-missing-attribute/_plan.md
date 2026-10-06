# Plan: Admin Section Missing Attribute

## Created
2026-10-06

## Status
completed

## Objective
Make `AdminSectionDiscovery` fail loudly with a helpful `AdminException` when a class lacks `#[AdminSection]`, and stop `discoverInModule()` from reporting files that only mention the attribute textually.

## Related Issues
Closes #313

## Discovery Notes
- `parseAdminSectionClass()` checks the interface first, then indexes `getAttributes(AdminSection::class)[0]` blindly, producing a PHP warning + `Error` when the attribute is absent.
- `discoverInModule()` selects files by `str_contains($content, '#[AdminSection')`, which matches comments/docblocks and longer attribute names like `#[AdminSectionWidget]`.
- `PermissionDiscovery::discoverFromClass()` (admin-auth) calls `parseAdminSectionClass()` and propagates exceptions since #309.
- `Marko\Core\Discovery\ClassFileParser` (extractClassName + loadClass) is the established file-to-class helper used by entity/command/observer discovery; `marko/admin` already requires `marko/core`.
- #314 (boot wiring) will touch the same file later; `discoverInModule()` keeps returning file paths here so #314 is free to reshape it.

## Scope

### In Scope
- `AdminException::missingSectionAttribute()` and its use in `parseAdminSectionClass()` (checked before the interface).
- Reflection confirmation in `discoverInModule()` after the text pre-filter (file -> class via `ClassFileParser`, skip when the class has no `#[AdminSection]`).
- admin-auth test proving `PermissionDiscovery` surfaces the new exception unchanged, plus re-pointing the existing interface test at an attribute-but-no-interface fixture (the existing `DiscoveryNotASection` fixture now hits the missing-attribute path).
- Docs page update in `admin.md` (section requirements + exceptions only; discovery internals are not documented until #314 wires them).

### Out of Scope
- Wiring discovery into boot, registry singletons, caching (#314).
- Changes to `AdminSectionDefinition` or attribute classes.

## Success Criteria
- [ ] Missing attribute throws `AdminException` naming the class with a suggestion; no warning or `Error`
- [ ] Class with neither attribute nor interface gets the missing-attribute exception
- [ ] Comment-only mention and `#[AdminSectionWidget]` files are not reported by `discoverInModule()`
- [ ] `PermissionDiscovery::discoverFromClass()` surfaces the new exception unchanged, and admin-auth still covers the interface path
- [ ] admin.md documents the exception (without documenting `discoverInModule`)
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Throw missingSectionAttribute from parseAdminSectionClass | - | completed |
| 002 | Confirm text matches with reflection in discoverInModule | 001 | completed |
| 003 | PermissionDiscovery surfaces the new exception | 001 | completed |
| 004 | Document AdminSection requirements and new exception in admin.md | 001 | completed |

## Architecture Notes
- `AdminSectionDiscovery` gains a promoted constructor dependency `ClassFileParser $classFileParser = new ClassFileParser()` so the container can autowire it and existing `new AdminSectionDiscovery()` call sites keep working.
- Text pre-filter stays (cheap); reflection is the authority. A class that really has the attribute but is invalid still throws at parse time.

## Risks & Mitigations
- Loading classes during discovery executes file-level code: same behaviour as every other Marko discovery (`ClassFileParser::loadClass`), and only for files that pass the text pre-filter.
- Task 002 and 001 touch the same file: run sequentially.
- Discovery now `require_once`s candidate files: temp-file test fixtures must use unique namespaces to avoid "Cannot declare class" fatals (task 002).
