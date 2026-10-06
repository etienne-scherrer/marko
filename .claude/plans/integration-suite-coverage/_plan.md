# Plan: Integration Suite Coverage

## Created
2026-10-05

## Status
completed

## Objective
Turn every remaining `->todo()` row in `tests/Integration/App/KnownGapsTest.php` for a merged ticket into a real integration test, and make the Integration CI job actually run the real-driver suites (pgsql, MySQL, live Redis) instead of skipping them.

## Related Issues
Closes #226

## Discovery Notes
- `KnownGapsTest.php` holds 18 todos for #159, #160, #161, #162, #164, #165, #168 and #169; all owning PRs are merged (#199, #192, #202/#241, #207, #193, #197, #201, #203). The #173 row mentioned in the issue is already gone.
- Fixture workarounds still describe the old bugs: `module.php` (#164 comment), `PrivatePropertyJob` docblock (#161), `config/cache-redis.php` and `config/database.php` read `getenv()` "because the fixture does not install marko/env" (#160 — `Application::boot()` now runs `EnvLoader`, which mirrors real env vars into `$_ENV`).
- Driver tests in `packages/database-{pgsql,mysql}/tests/Integration` are in group `integration` (also used by an unrelated devai test) and gated on `MARKO_TEST_PGSQL_*` / `MARKO_TEST_MYSQL_*`, which CI never sets. No MySQL service exists anywhere in CI.
- `packages/broadcasting-amphp/tests/Feature/RedisLiveTest.php` has no group and no `MARKO_INTEGRATION_REQUIRED` handling; `packages/pubsub-redis/tests/Integration/SharedConnectionLiveTest.php` is the model.
- Decision: rename the driver tests' group to `integration-services` (one group for every service-backed test; avoids pulling devai's `integration` test into the job), and make every gated suite throw instead of skip when `MARKO_INTEGRATION_REQUIRED` is set.
- Driver pgsql tests use their own `marko_test` database on the shared Postgres service, because the fixture suite drops `marko_integration` with `FORCE` before every case.
- Remember-me across requests is exercised worker-style. Task 007 makes the fixture's user provider an explicit singleton: today it is a plain binding held by the singleton AuthManager, and FakeUserProvider keeps tokens in memory. Between requests the test calls `RequestStateResetter::reset()`, which is what a long-running worker does.
- `EnvLoader` mirrors real env into `$_ENV` with `??=` (never overwrites), and `$_ENV` persists per process. Env-driven tests must use unique variable names and clean up `$_ENV` and `putenv` (task 003).
- Branch protection (making `Integration` required) is a maintainer action; the PR asks for it rather than doing it.

## Scope

### In Scope
- Real integration tests for every #159/#160/#161/#162/#164/#165/#168/#169 todo, in topical files; delete `KnownGapsTest.php`
- Remove the matching fixture workarounds
- MySQL service + `MARKO_TEST_*` env + pgsql driver database in the Integration job; `pdo_mysql`
- Driver tests and `RedisLiveTest` in `integration-services`, with fail-not-skip under `MARKO_INTEGRATION_REQUIRED`
- `tests/CiWorkflowTest.php` assertions, `tests/Integration/compose.yml` MySQL, `.claude/testing.md`, `CLAUDE.md`, `.claude/pr-review-process.md`

### Out of Scope
- Changing branch protection (maintainer action)
- Any production code changes beyond what a failing test proves is broken

## Success Criteria
- [x] No `->todo()` remains for the eight tickets; `KnownGapsTest.php` removed
- [x] `composer test:integration` passes locally against Postgres, MySQL and Redis with no skips in the touched suites
- [x] CiWorkflowTest asserts the MySQL service and env vars
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | CI workflow, compose and CiWorkflowTest for MySQL + driver env | - | completed |
| 002 | Driver integration tests and RedisLiveTest join integration-services | 001 | completed |
| 003 | Shared connection (#159) and real env vars (#160) | 002 | completed |
| 004 | Queue: failed_jobs, NUL payload, priority, backoff (#161, #162) | 003 | completed |
| 005 | Scheduler: schedule:run and overlap (#164) | 004 | completed |
| 006 | Rate limiter on redis, IPv6, per-route (#165) | 005 | completed |
| 007 | Remember-me and auth events (#168) | 006 | completed |
| 008 | Exception mapping 422/404/419 (#169) | 007 | completed |
| 009 | Remove KnownGapsTest, docs (testing.md, CLAUDE.md, pr-review-process.md) | 008 | completed |

## Architecture Notes
- Tasks 003-008 all edit the shared fixture (`Helpers.php` module list, `module.php`, controllers) and use one Postgres database per process, so they run sequentially.
- New files: `pest()->group('integration-services')`, `setUpIntegrationTest()` / `tearDownIntegrationTest()`.
- Redis is shared and never flushed: rate-limit tests use a random client IP per test, IPv4 and IPv6 alike.
- `integrationRequest()` gains optional `query`/`post` parameters (task 007 or 008, whichever needs them first).

## Risks & Mitigations
- A converted test exposes a real bug in a merged ticket: fix it test-first in the owning package and call it out in the PR.
- CI wall time: services start in parallel; the job runs serially (driver tests share databases); MySQL adds roughly 20s of startup.
