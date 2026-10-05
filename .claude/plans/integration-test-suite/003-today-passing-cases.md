# Task 003: Cases That Pass Today

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Implement the service-backed cases that pass on current develop: migrations, repository round-trip, seeder, Redis
cache, a routed request through global session middleware, and a successful job processed by `queue:work --once`.

## Context
- Related files: `tests/Integration/App/*Test.php`
- Commands run through `Application::$commandRunner`; requests through `$app->router->handle()`

## Requirements (Test Descriptions)
- [x] `it applies the fixture migrations with db:migrate`
- [x] `it round-trips an entity through its repository on postgres`
- [x] `it runs the fixture seeder with db:seed`
- [x] `it stores and reads a value through the redis cache driver`
- [x] `it routes a request through the global session middleware and persists the session row`
- [x] `it processes a successful job with queue:work --once`

## Acceptance Criteria
- Green locally against compose services and in the CI Integration job
- Each case starts from a freshly reset database, so cases other than the migrate case apply migrations through the
  harness first
- The Redis cache case assumes `127.0.0.1:6379`, because cache-redis ignores config (#166). Its keys are unique per
  test; no FLUSHDB.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
