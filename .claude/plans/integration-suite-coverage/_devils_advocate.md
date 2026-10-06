# Devil's Advocate Review: integration-suite-coverage

## Critical (Must fix before building)

1. **Task 004: the max_attempts test and the configured backoff contradict each other.** Task 004 configures a fixture `queue.backoff`, and the failed_jobs test runs `queue:work --once` three times. Once a backoff is set, every failed attempt pushes `available_at` into the future (`DatabaseQueue` filters on `available_at <= now`), so runs 2 and 3 find nothing and the job never reaches `failed_jobs`. Fix: between runs, the test rewinds `available_at` to the past with direct SQL (the todo note calls this "releasing its delay between runs"). The test must not rely on a zero backoff.

## Important (Should fix before building)

2. **Task 004: an exact backoff check will be flaky.** `DatabaseQueue` uses wall-clock `new DateTimeImmutable()` (no clock injection) and stores `available_at` as `Y-m-d H:i:s`. A check of "exactly that backoff" can fail by one second. Fix: take a timestamp before and after the failing run and assert `available_at` falls between `before + backoff` and `after + backoff`. Use a backoff that clearly differs from the `2^attempts * 10` default for the attempt under test (for attempt 1 the default is 20s, so 7s works).
3. **Task 003: `EnvLoader` never overwrites `$_ENV` (`$_ENV[$name] ??= $value`), and `$_ENV` lives for the whole process.** If ConfigTest reuses a variable name, or leaves a stale `$_ENV` entry, it passes without testing anything. The task as written also has three more gaps:
   - The test cannot change `variables_order` at runtime. It has to simulate it by `unset($_ENV[$name])` before boot.
   - It must call `putenv()` *before* booting, so it cannot use the boot-first `setUpIntegrationTest()` beforeEach.
   - In CI, `DB_*` values equal the config defaults (`127.0.0.1`/`marko`). So converting `database.php`/`cache-redis.php` to `$_ENV` is not proven by the other tests; ConfigTest is the only real proof.
   Fix: use a unique variable name, unset it from `$_ENV` first, build the project and call `bootIntegrationApp()` manually, and clean both `$_ENV` and `putenv` in a `finally`.
   Also: `DatabaseTestingFixture/config/database.php` uses `getenv()` too. Only convert it if its boot path runs `EnvLoader`.
4. **Task 005: a task using `withoutOverlapping()` with no `description()` breaks every `schedule:run`.** `ScheduleRunner` validates all overlap-protected tasks up front, so the heartbeat test would fail too. Fix: the new task must have a description, and the heartbeat test must use `toContain` rather than an exact output match, because both tasks run every minute. The lock can be held through the container's `TaskMutexInterface` singleton (its `handles` map makes `acquire()` return false), or through a second `FileTaskMutex` on `{project}/storage/framework`.
5. **Task 006: the IPv6 address is fixed in the todo (`2001:db8::1`).** Redis is never flushed, so a rerun inside the decay window starts already limited. Fix: randomise the IPv6 client address as well, not only IPv4.
6. **Task 007: worker-style reset.** `Marko\Core\RequestStateResetter::reset()` already exists; use it instead of hand-rolling the reset.
   - The cookie is named `remember_session`: prefix from `authentication.remember.cookie.prefix` + guard name `session`.
   - Events only reach an observer when something durable records them across requests. Use a singleton recorder or a file under `{project}/storage`.
   - `UserProviderInterface` is a plain binding today. It survives only because the singleton `AuthManager` holds it, and the plan's discovery note calls it a singleton. Fix: list it under `singletons` in fixture `module.php` so the remember-token store is guaranteed to survive.
7. **Task 008 (and 007 if login is POST): `integrationRequest()` cannot carry input.** It only accepts `$cookies` and `$server`, and `Request` does not parse `REQUEST_URI` into `query`. Fix: add optional `query`/`post` parameters to `integrationRequest()` in `Helpers.php`. Send the wrong CSRF token as `HTTP_X_CSRF_TOKEN`. The CSRF route must be POST (`CsrfMiddleware` lets GET through).
8. **Task 002: a throw inside a `->skip(fn () => ...)` closure is not a reliable "fail".** Every pgsql/MySQL `ConstraintViolationTest`, `ModifyColumnMigrationTest` and `SharedConnectionTransactionTest` uses that pattern, each with its own duplicated `*Config()` function and `*_SKIP_REASON` constant. `Fixtures/IntegrationDatabase` now exists in both packages. Fix: route every file through `IntegrationDatabase::config()` from a `beforeEach` (or the test body) that calls `markTestSkipped`, so the exception surfaces as a test error. Delete the per-file helper functions and constants so they don't collide. `deadlock-contender.php` keeps reading the variables directly, since the subprocess inherits the environment.

## Minor (Nice to address)

- Task 001: a service container cannot mount `postgres-init/`, so `marko_test` needs a step. `psql` is on ubuntu-latest, but `docker exec ${{ job.services.postgres.id }} createdb -U marko marko_test` avoids depending on it. The driver tests default `MARKO_TEST_PGSQL_USERNAME` to `postgres`, so CI must set it to `marko`.
- Task 001: MySQL 8.4 defaults to `caching_sha2_password`. pdo_mysql handles it over plain TCP via RSA key exchange (needs openssl). If auth fails, add `--mysql-native-password=ON` or use SSL.
- Tasks 003-008: remove each converted row from `KnownGapsTest.php` in the same task, so the file never holds a todo and a real test under the same description at once. Task 009 then only deletes the empty file.
- Task 009: a "no todo rows" test that greps for `->todo(` will also match itself; scope it to `issue:` rows for the eight tickets.

## Questions for the Team

- Compose already defines MySQL, and `IntegrationDatabase` fixtures exist in this worktree while tasks 001/002 are still `pending`. Is execution already under way? If so, the orchestrator should resync statuses.
- Should `Integration` become a required check now or after a few green runs? (Maintainer action; the plan leaves it to the PR.)
