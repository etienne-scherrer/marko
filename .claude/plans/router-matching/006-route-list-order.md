# Task 006: route:list in effective match order

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make `route:list` print routes grouped by method in the order the matcher tries them.

## Context
- Related files: packages/routing/src/Commands/RouteListCommand.php, tests/Commands/RouteListCommandTest.php

## Requirements (Test Descriptions)
- [x] `it lists static routes before dynamic routes of the same method`
- [x] `it groups routes by method in a fixed method order`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
