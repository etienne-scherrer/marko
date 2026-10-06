# Task 005: Redis skip probes and docs-fts test cleanups

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
The Redis integration skip probes trigger Predis's internal `@stream_socket_client` warning, and the skip message doesn't say why Redis is unreachable. docs-fts test cleanups `@unlink` files that may not exist. Make both warning-free.

## Context
- Related files: packages/cache-redis/tests/Integration/RedisCacheDriverIntegrationTest.php, packages/ratelimiter/tests/Integration/RedisRateLimiterTest.php, packages/docs-fts/tests/Unit/Indexing/FtsIndexBuilderTest.php
- Probe: memoized `...UnreachableReason(): ?string` that pings via Predis inside `ErrorCapture::run()` and returns the exception message; skip message includes it.

- Memoization gotcha: null means "reachable", so `static $reason = null; if ($reason === null)` would re-ping on every call when Redis is up. Use a separate `static bool $probed = false`. Keep `...Unavailable(): bool` as `reason !== null` so the existing `->skip(fn () => ..., ...)` calls stay valid.
- The skip message is evaluated at definition time (Pest's `skip()` message is a string), so the probe runs on file load; that is accepted (0.5s timeout, memoized).

## Requirements (Test Descriptions)
- [x] Redis probes raise no PHP warning when Redis is down (verified via `--display-warnings` run)
- [x] Skip reason reads `Redis is not reachable at host:port: <reason>. ...`
- [x] docs-fts cleanups use `if (is_file($dbPath)) { unlink($dbPath); }`

## Acceptance Criteria
- Scoped runs of the three test files show 0 warnings

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
