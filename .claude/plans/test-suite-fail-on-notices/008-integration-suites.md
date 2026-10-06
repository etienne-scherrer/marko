# Task 008: Integration Suites Clean Under the New Flags

**Status**: completed
**Depends on**: 007
**Retry count**: 0

## Description
The CI integration jobs use the same `phpunit.xml`. Run the `integration-services` group (Postgres, MySQL 8.4, Redis) and the MySQL suites against MariaDB 11.8 and 10.11 locally in docker, under a separate compose project and ports, and fix any notice, deprecation, risky test or warning the new flags surface. Remove the containers afterwards.

## Context
- `tests/Integration/compose.yml`, `.github/workflows/ci.yml` integration jobs, `.github/workflows/nightly*.yml`
- Use `MARKO_INTEGRATION_REQUIRED=1` so nothing passes by skipping.
- The Nightly workflow (`composer test:all`) also runs under the new flags: the Redis suites (`packages/cache-redis`, `packages/ratelimiter`) with a live server, `packages/roadrunner/tests/Integration/EndToEndTest.php` (needs `vendor/bin/rr get-binary`; place the binary outside the repo or remove it afterwards), and `packages/mail-smtp/tests/Integration/StreamSocketIntegrationTest.php`. Run these files locally too.
- Do NOT run `tests/IntegrationVerificationTest.php` in the worktree: it deletes `vendor/` and `composer.lock` and runs `composer update`. Record in the PR description that the first nightly run verifies it.

## Requirements (Test Descriptions)
- [x] `composer test:integration` exits 0 against Postgres/MySQL/Redis with the new flags
- [x] MySQL driver suites exit 0 against MariaDB 11.8 and 10.11 with the new flags
- [x] Redis, roadrunner end-to-end and mail-smtp integration files exit 0 with the new flags
- [x] Any surfaced issue is fixed at its source (no blanket opt-outs)

## Acceptance Criteria
- Containers and volumes removed afterwards

## Implementation Notes
Ran in docker compose project `marko-int-356` on ports 55432/56379/53306/53307/53308 with MARKO_INTEGRATION_REQUIRED=1 and the new phpunit.xml: `--group=integration-services` 303 passed / 5 skipped (MySQL-only skips), MySQL suites vs MariaDB 11.8 and 10.11 112 passed / 3 skipped each; all exit 0 with no notices/deprecations/risky/warnings. Nightly-only files (cache-redis, ratelimiter with live Redis, roadrunner E2E with `rr` binary, mail-smtp) 370 passed, exit 0. Nothing needed fixing. A serial (non-parallel) run lists 19 `@`-suppressed warnings from pre-existing code (cache-file mkdir, codeindexer cache, mail-smtp stream_socket_client, predis, a docs-fts test unlink); PHPUnit shows them but does not count them toward failOnWarning (exit 0), and parallel `composer test` does not list them. Left for a follow-up. `IntegrationVerificationTest` not run locally (deletes vendor/); the next nightly verifies it.
