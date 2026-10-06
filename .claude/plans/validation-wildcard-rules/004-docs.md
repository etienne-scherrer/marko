# Task 004: Docs page and README

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Document wildcard keys (array items, nested arrays, multi-file uploads, missing parents, scalars, literal `*`, cross-field rules) in the validation docs page, replacing the "Per-file rules are not supported yet" note, and keep the README slim.

## Context
- Related files: packages/docs-markdown/docs/packages/validation.md, packages/validation/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `it documents wildcard keys on the validation docs page`
- [x] `it no longer says per-file rules are unsupported`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
New "Validating Arrays with Wildcards" section; the README quick example shows a `photos.*` rule.
