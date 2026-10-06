# Plan: Capture Suppressed Error Reasons

## Created
2026-10-06

## Status
completed

## Objective
Replace `@` error suppression in mail-smtp, cache-file and codeindexer with a shared scoped error-capturing helper in marko/core, so failures carry the OS reason in their exceptions and a serial `--display-warnings` run shows 0 warnings from Marko code.

## Related Issues
Closes #358

## Discovery Notes
- PR #357 added a private `capturing(?string &$reason, callable $operation)` to `Marko\Core\Discovery\DiscoveryCache` (scoped `set_error_handler()`, restored in `finally`).
- mail-smtp, cache-file and codeindexer all already require `marko/core`, so one shared helper `Marko\Core\Support\ErrorCapture` fits; DiscoveryCache switches to it.
- `TransportException` (marko/mail) has `connectionFailed($host, $port)` and `tlsFailed($host)` without a reason.
- `FileCacheDriver::ensureDirectoryExists()` calls `@mkdir` on every write; `write()` returns `false` silently on rename/temp-file failure. `CacheException` (marko/cache) is the base for driver exceptions (`RedisConnectionException`).
- `IndexCacheException::cacheDirUnwritable($path)` has no reason; `IndexCache` uses `@` for mkdir/file_put_contents/unserialize/file_get_contents/unlink.
- Redis integration skip probes trigger Predis's internal `@stream_socket_client`; skip messages are built at definition time.
- docs-fts test cleanups use `@unlink` on files that may never exist.

## Scope

### In Scope
- `Marko\Core\Support\ErrorCapture` helper + DiscoveryCache refactor onto it
- mail-smtp connect/STARTTLS reasons; `TransportException::connectionFailed(..., ?string $reason = null)` and `tlsFailed(..., ?string $reason = null)`
- cache-file: no mkdir when the dir exists, `FileCacheException` with reasons for directory creation and write failures
- codeindexer: reasons in `IndexCacheException::cacheDirUnwritable()`, warning-free corrupt-cache rebuild, loud invalidate failure
- Redis skip probes: no warnings, reason in skip message
- docs-fts test cleanups without `@`
- Docs pages for mail-smtp, cache-file, codeindexer (and core helper mention)

### Out of Scope
- `@posix_kill` in devserver, `@unlink` in filesystem-local/page-cache-file cleanup, routing `UploadedFile`, queue `FailedCommand`, other test cleanups (listed in PR as follow-ups)

## Success Criteria
- [x] Serial `pest --exclude-group=integration-destructive --display-warnings` run shows 0 warnings; parallel `composer test` too
- [x] StreamSocket connect failure exception carries OS reason (tested)
- [x] STARTTLS failure raises no raw warning; tlsFailed includes reason
- [x] FileCacheDriver never calls mkdir() when dir exists; failures throw with reason
- [x] IndexCacheException includes reason; corrupt cache rebuilds with no warning
- [x] Redis probes emit no warning; skip reason explains why
- [x] No `@` left at the listed sites
- [x] Docs updated
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | ErrorCapture helper in marko/core; DiscoveryCache uses it | - | completed |
| 002 | mail-smtp connect and STARTTLS reasons | 001 | completed |
| 003 | cache-file directory and write failure reasons | 001 | completed |
| 004 | codeindexer IndexCache reasons and warning-free rebuild | 001 | completed |
| 005 | Redis skip probes and docs-fts test cleanups | 001 | completed |
| 006 | Docs pages and testing guide | 002, 003, 004 | completed |

## Architecture Notes
- `ErrorCapture::run(?string &$reason, callable $operation): mixed` — static, stateless, generic via `@template`. Installs a handler that records every raised message (joined with `; `), returns the operation's result, always restores the previous handler in `finally`.
- New `?string $reason = null` parameters stay optional for BC.
- Reason is appended to the exception `context` as `": $reason"`, matching `DiscoveryCacheException::notWritable()`.
- Failure tests block paths with files/directories instead of `chmod` wherever possible (CI may run as root). Where `chmod` is unavoidable (IndexCache `invalidate()`), skip when running as root. "No warning" assertions record every error-handler call, not only those passing `error_reporting()`.
- BC changes: `FileCacheDriver` throws `FileCacheException` (extends `CacheException`, not `RuntimeException`) where it used to return `false` or throw `RuntimeException`; `IndexCacheInterface::invalidate()` gains `@throws IndexCacheException` (new `cacheNotRemovable()` factory). Both are documented in task 006.

## Risks & Mitigations
- Throwing from `FileCacheDriver::set()` where it used to return `false`: documented in PR and docs; loud-errors principle.
- Skip messages evaluated at file load run the Redis probe eagerly: probe has a 0.5s timeout and is memoized.
