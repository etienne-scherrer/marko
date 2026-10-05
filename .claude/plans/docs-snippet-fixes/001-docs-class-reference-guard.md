# Task 001: Docs Class-Reference Guard Test

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `tests/DocsClassReferenceTest.php`, which scans package READMEs and every docs-markdown page (recursively), extracts `Marko\...` references inside fenced PHP code blocks, resolves them through the PSR-4 maps in `packages/*/composer.json`, and fails with file:line and the missing class.

## Context
- Related files: tests/DocsClassReferenceTest.php, tests/Support/DocsClassReference/DocsClassReferenceScanner.php, tests/Fixtures/DocsClassReference/
- Patterns to follow: tests/Psr7ContainmentTest.php (support class + fixtures)

## Requirements (Test Descriptions)
- [x] `it resolves every Marko class referenced in README and docs PHP snippets`
- [x] `it discovers package READMEs and nested docs pages`
- [x] `it reports a bogus Marko class added to a README with its file and line`
- [x] `it ignores Marko references outside fenced PHP code blocks`
- [x] `it handles indented fences, group uses, FQCNs, namespaces and function imports`
- [x] `it skips references under an allowlisted fictional namespace prefix`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Namespace declarations and `use function` / `use const` imports are skipped (PSR-4 maps classes only). The allowlist is supported but empty, because the fictional examples were moved out of the `Marko\` namespace in task 002.
