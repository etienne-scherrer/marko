# Plan: Webhook Timeout and Response Body Cap

## Created
2026-10-06

## Status
completed

## Objective
Make `webhook.timeout` actually bound outgoing webhook requests, reject a non-positive timeout loudly, and cap every recorded response body (success and rejection) with `HttpResponse::bodyExcerpt()`.

## Related Issues
Closes #293

## Discovery Notes
- `WebhookConfig` reads `webhook.timeout` but nothing consumes it; `WebhookReceiver` is its only consumer (for `timestampTolerance`).
- `WebhookDispatcher` posts with `HEADERS`, `BODY`, `HTTP_ERRORS` only; its constructor takes `HttpClientInterface` and `ClockInterface`.
- `marko/http` `RequestOptions::TIMEOUT` exists; Guzzle's default `0` means wait forever.
- `WebhookDeliveryService::recordRejection()` caps the body via `HttpResponse::bodyExcerpt()`; `recordSuccess()` stores it raw into a MySQL `TEXT` column.
- `DispatchWebhookJob` already records any `Throwable` from `dispatch()` as a failure and retries it; `DispatchWebhookJobResponseTest` covers a `ConnectionException('Connection refused')`.
- Config validation pattern: `ConfigException(message, context, suggestion)` as in `TokenConfig`.

## Scope

### In Scope
- Inject `WebhookConfig` into `WebhookDispatcher` and pass `RequestOptions::TIMEOUT`
- `WebhookConfig` throws `ConfigException` naming `webhook.timeout` when `timeout <= 0`
- Shared private `excerpt()` helper in `WebhookDeliveryService` used by `recordSuccess()` and `recordRejection()`
- Update all `new WebhookDispatcher(...)` call sites in tests
- Test that a timed-out request is recorded as a failure and retried
- Docs: Configuration, `WebhookDispatcher`, `WebhookDeliveryService`, `WebhookAttempt` sections

### Out of Scope
- A `connect_timeout` config key (Guzzle's `timeout` already bounds the whole request including connect; #289 will rework `config/webhook.php`, so the config file stays untouched)
- Changing the Guzzle driver's defaults

## Success Criteria
- [ ] `WebhookDispatcher` passes `RequestOptions::TIMEOUT` from `webhook.timeout`
- [ ] A non-positive `webhook.timeout` throws a config exception naming the key
- [ ] `recordSuccess()` caps `response_body` like `recordRejection()`
- [ ] A timed-out `ConnectionException` is recorded as a failure and retried
- [ ] Docs updated
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Validate webhook.timeout in WebhookConfig | - | completed |
| 002 | Pass webhook.timeout to the HTTP client in WebhookDispatcher | 001 | completed |
| 003 | Cap every recorded response body in WebhookDeliveryService | - | completed |
| 004 | Update webhook docs page | 001, 002, 003 | completed |

## Architecture Notes
- `WebhookDispatcher` is autowired via `module.php` binding; adding `WebhookConfig` (a concrete autowirable class) to its constructor needs no binding change.
- Missing `webhook.timeout` keeps failing with `ConfigNotFoundException` via `getInt()`.

## Risks & Mitigations
- Constructor change breaks callers constructing `WebhookDispatcher` manually: only tests do; update them all.
- `WebhookConfig` reads all four keys eagerly: test call sites must pass a full four-key config to it, separate from the job's retry config (one job test uses `config: []`).
- `bodyExcerpt()` trims, so successful bodies are now stored trimmed; pinned by a test in 003 and documented in 004.
