# Task 004: Docs

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Describe lazy building and when each error is raised in admin.md ("How Sections Are Discovered", "Querying Sections", API reference, exceptions table); update the RoadRunner state-leak verdict and the tutorial wording.

## Context
- Related files: packages/docs-markdown/docs/packages/admin.md, packages/docs-markdown/docs/packages/roadrunner-state-leaks.md, packages/docs-markdown/docs/tutorials/build-an-admin-panel.md, packages/admin/README.md

## Requirements (Test Descriptions)
- [x] `admin.md explains sections are built on first use, once per process`
- [x] `admin.md lists which errors fail at boot and which at first use`
- [x] `roadrunner-state-leaks.md verdict reflects lazily built, worker-lived instances`

## Acceptance Criteria
- Docs accurate to the code

## Implementation Notes
