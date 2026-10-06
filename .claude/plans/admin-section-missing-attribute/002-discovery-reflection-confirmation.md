# Task 002: Confirm text matches with reflection in discoverInModule

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
After the cheap `#[AdminSection` text pre-filter, resolve each candidate file to its class with `ClassFileParser`, load it, and keep the file only when the class actually carries `#[AdminSection]`. Files that only mention the attribute in comments, or use a longer attribute name, are skipped instead of failing at parse time.

## Context
- Related files: packages/admin/src/Discovery/AdminSectionDiscovery.php, packages/core/src/Discovery/ClassFileParser.php
- Patterns to follow: packages/database/src/Entity/EntityDiscovery.php (ClassFileParser injection)
- Add promoted ctor param `private ClassFileParser $classFileParser = new ClassFileParser()` (pattern already used in `packages/core/src/Application.php:104`; keeps `new AdminSectionDiscovery()` call sites in admin and admin-auth tests working)
- Mirror `EntityDiscovery::discoverInPath()`: `extractClassName()` -> `continue` on `null` (before any require) -> `loadClass()` -> `continue` on `false` -> `ReflectionClass::getAttributes(AdminSection::class)` -> `continue` when empty. Do NOT check the interface here; that stays a loud parse-time error.
- `loadClass()` returns false for interfaces/traits (class_exists is false) and for missing Marko-package deps; it re-throws `Error` for non-Marko missing deps. That loud re-throw is intended.
- Discovery now `require_once`s every candidate file, so every temp-file fixture class gets declared in the test process. Each new fixture MUST use its own unique namespace (e.g. `AdminDiscoveryTestComment`, `AdminDiscoveryTestWidget`, `AdminDiscoveryTestNoClass`, `AdminDiscoveryTestNoInterface`) or PHP fatals with "Cannot declare class".
- Fixtures using `#[AdminSectionWidget(...)]` load fine without that attribute class existing (attributes resolve lazily); do not call `newInstance()` on them.

## Requirements (Test Descriptions)
- [ ] `it skips files that mention AdminSection only in a comment`
- [ ] `it skips files whose attribute name only starts with AdminSection`
- [ ] `it skips files that declare no class`
- [ ] `it still reports a file whose class has the attribute but does not implement the interface`

## Acceptance Criteria
- All requirements have passing tests
- Existing discovery tests still pass
- Code follows code standards

## Implementation Notes
Implemented with strict TDD (tests written first and observed failing). See the commit for the code.
