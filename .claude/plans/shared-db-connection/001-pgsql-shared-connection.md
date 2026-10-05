# Task 001: Share connection and bind TransactionInterface in pgsql module

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Mark `ConnectionInterface` as a list-style singleton in `packages/database-pgsql/module.php` and bind `TransactionInterface` to a closure returning the shared connection, so every repository, the query builder factory and any service injecting `TransactionInterface` use one PDO handle.

## Context
- Related files: packages/database-pgsql/module.php, packages/database-pgsql/tests/Module/
- Patterns to follow: DialectOverrideTest builds a Container from real manifests via BindingRegistry

## Requirements (Test Descriptions)
- [x] `it resolves the same ConnectionInterface instance for two repositories`
- [x] `it gives the QueryBuilderFactoryInterface the same connection the repositories use`
- [x] `it resolves TransactionInterface to the shared ConnectionInterface instance`
- [x] `it throws a loud error when the bound connection does not implement TransactionInterface`

## Acceptance Criteria
- All requirements have passing tests
- Existing `bindings` entry for ConnectionInterface is unchanged

## Implementation Notes
(Left blank - filled in by programmer during implementation)
