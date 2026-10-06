# Task 003: Dependent Packages

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update the interface stubs in marko/admin-panel tests and the `new AdminSectionRegistry()` calls in marko/admin-api tests; confirm `AdminSectionBootTest` still serves attribute-only sections through the real router.

## Context
- Related files: packages/admin-panel/tests/Unit/Controller/DashboardControllerTest.php, packages/admin-panel/tests/Unit/Menu/AdminMenuBuilderTest.php, packages/admin-api/tests/Unit/Controller/SectionControllerTest.php, packages/admin-api/tests/Unit/Controller/SectionControllerWiringTest.php (line 46), packages/admin-api/tests/Feature/AdminApiErrorShapeTest.php (line 93), packages/admin-api/tests/Feature/AdminSectionBootTest.php

## Requirements (Test Descriptions)
- [x] `existing admin-panel tests pass with stubs implementing registerDefinition`
- [x] `existing admin-api controller tests pass with a container-backed registry`
- [x] `AdminSectionBootTest returns attribute-only sections through the real router`

## Acceptance Criteria
- admin-panel and admin-api suites green

## Implementation Notes
