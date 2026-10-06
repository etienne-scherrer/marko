# Task 005: TruncateDatabase

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
truncate() empties every table backed by a discovered entity (extenders skipped, existing tables only), leaving the migrations table alone. PostgreSQL: one TRUNCATE ... RESTART IDENTITY CASCADE; MySQL: FK checks off around per-table TRUNCATE. Refuses inside an open transaction and for unknown drivers.

## Requirements (Test Descriptions)
- [ ] `it refuses to truncate inside an open transaction`
- [ ] `it throws for an unsupported driver`
- [ ] `it lists only entity tables that exist`
- [ ] `it issues one TRUNCATE RESTART IDENTITY CASCADE on pgsql`
- [ ] `it disables and re-enables foreign key checks around per-table TRUNCATE on mysql even when a truncate fails`
- [ ] `it never includes the migrations table`
- [ ] `it runs no SQL when there are no entity tables`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Integration (task 007) runs Postgres only, so both drivers' SQL must be asserted here with a fake connection. Re-enable MySQL FK checks in a `finally`.
- Discover entity tables the same way `MigrateCommand` does (`EntityDiscovery::discoverAll()` + `EntityMetadataFactory`), not a new scanner.
