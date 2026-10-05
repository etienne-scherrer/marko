# Task 001: Expectations plugin class, composer declaration, shim

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Move the six expectation registrations into `Marko\Testing\Pest\ExpectationsPlugin` (a Pest `Bootable` plugin), declare it in `extra.pest.plugins`, drop the `autoload.files` entry, and reduce `src/Pest/Expectations.php` to a shim.

## Context
- Related files: packages/testing/src/Pest/Expectations.php, packages/testing/composer.json, tests/Pest.php, packages/testing/tests/PackageStructureTest.php
- Patterns to follow: vendor/pestphp/pest/composer.json `extra.pest.plugins`

## Requirements (Test Descriptions)
- [x] `it declares ExpectationsPlugin in composer.json extra.pest.plugins`
- [x] `it implements the Pest Bootable plugin contract`
- [x] `it no longer lists the expectations file in autoload.files`
- [x] `it suggests pestphp/pest for the expectations`
- [x] `it registers every expectation when the plugin boots`
- [x] `it registers every expectation when the backward-compatible shim is required`

## Acceptance Criteria
- All requirements have passing tests
- No `function_exists('expect')` guard remains

## Implementation Notes
Monorepo `tests/Pest.php` no longer requires the file; the suite relies on the plugin via `vendor/pest-plugins.json`.
