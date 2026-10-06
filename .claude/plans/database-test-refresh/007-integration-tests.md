# Task 007: Integration tests against real Postgres

**Status**: completed
**Depends on**: 004, 005, 006
**Retry count**: 0

## Description
integration-services tests using a dedicated fixture project and database covering the ticket's exit criteria.

## Requirements (Test Descriptions)
- [ ] `it inserts a unique row in the first test`
- [ ] `it inserts the same unique row in the second test`
- [ ] `it runs migrations once per process`
- [ ] `it lets code under test open its own transaction`
- [ ] `it keeps the test transaction across TestClient requests`
- [ ] `it runs after-commit callbacks only when asked`
- [ ] `it truncates entity tables and keeps migration bookkeeping`
- [ ] `it creates entities through a factory with repository events`

## Acceptance Criteria
- All requirements have passing tests
- Passes under `composer test` (parallel, TEST_TOKEN) and `composer test:integration`

## Implementation Notes
- Do NOT use `setUpIntegrationTest()`/`bootIntegrationApp()`: they build a new random project path and `DROP DATABASE ... WITH (FORCE)` before every test, which defeats once-per-process caching and would kill TestDatabase's cached connection if another integration file runs later in the same process.
- Dedicated fixture `tests/Integration/App/RefreshFixture/` with `config/database.php` using `integrationDatabaseName($env) . '_refresh'` (keeps the TEST_TOKEN suffix for parallel workers) and a small entity + committed migration with a unique column.
- Once per process (static guard): skip via `integrationServicesSkipReason()`, drop/create the `_refresh` database with the same PDO approach as `resetIntegrationDatabase()`, `buildIntegrationProject(<RefreshFixture path>)`, and remove the project at shutdown.
- Set `APP_ENV=testing` for the duration (unset = production, which TestDatabase refuses).
- "Migrations once per process": assert the migrations table has a single batch after several `TestDatabase::boot()` calls for the same path.
- The truncate test must not run inside a RefreshDatabase transaction (truncate refuses); put it in its own file or test without RefreshDatabase.
