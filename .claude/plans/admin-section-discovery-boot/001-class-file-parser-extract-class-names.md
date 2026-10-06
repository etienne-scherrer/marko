# Task 001: ClassFileParser::extractClassNames

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `ClassFileParser::extractClassNames()` returning every named class, interface, trait and enum declared in a PHP file, each qualified with the namespace in effect where it is declared. `extractClassName()` keeps returning the first one. Admin section discovery needs this to check every class in a file, not just the first.

## Context
- Related files: packages/core/src/Discovery/ClassFileParser.php, packages/core/tests/Unit/Discovery/ClassFileParserTest.php
- Patterns to follow: the existing token loop in `extractClassName()` (skips `::class` and anonymous classes)

## Requirements (Test Descriptions)
- [x] `it extracts every class declared in a file`
- [x] `it qualifies each class with the namespace it is declared in`
- [x] `it skips anonymous classes and ::class references when extracting every class`
- [x] `it returns an empty list for a file with no class`
- [x] `it keeps extractClassName returning the first declared class`
- [x] `it qualifies classes in braced namespace blocks with their own block's namespace`
- [x] `it returns an unqualified name for a class in the global namespace block`

## Interface Contract
- `public function extractClassNames(string $filePath): array` returning `array<int, string>` in declaration order; returns `[]` for a missing/unreadable file (mirrors `extractClassName()` returning null).
- An empty namespace name (`namespace { ... }`) must not produce a leading `\`.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
