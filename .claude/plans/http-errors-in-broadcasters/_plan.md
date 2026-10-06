# Plan: HTTP Errors in Broadcasters and Webhooks

## Created
2026-10-05

## Status
completed

## Objective
Make the Mercure and Pusher broadcasters and the webhook dispatcher read 4xx/5xx responses themselves (`http_errors => false`) so failures report the server's status and a capped body, with status-aware suggestions, no leaked Pusher signatures, and a documented webhook retry policy.

## Related Issues
Closes #264

## Discovery Notes
- `HttpClientInterface` throws `HttpException` for 4xx/5xx unless `http_errors => false`; `FakeHttpClient` mirrors this, which hid the bug.
- `MercureBroadcaster::sendRequest()` / `PusherBroadcaster::sendRequest()` already have `isSuccessful()` branches that never ran for 4xx/5xx.
- Guzzle's `RequestException`/`ConnectException` messages contain the full request URI, which for Pusher includes the signed query (`auth_signature`, `body_md5`, ...). The Guzzle exception also sits in the `previous` chain.
- `WebhookDispatcher::dispatch()` throws on 4xx/5xx; `DispatchWebhookJob` records `$e->getMessage()` and records any non-throwing response (even 3xx) as success.
- #228 (presence) and #221 (clock) are merged; the broadcasters already use `ClockInterface`.

## Scope

### In Scope
- `HttpResponse::bodyExcerpt()`: UTF-8-safe capped body shared by all three senders
- `BroadcastException::rejected()` with status-aware suggestions
- Mercure and Pusher: `http_errors => false`, rejected responses, transport-failure messages
- Pusher: redact the signed query string from transport-failure messages; never chain an exception that carries it
- Webhook: `http_errors => false`, `WebhookResponse::isRetryable()`, `WebhookDeliveryService::recordRejection()`, job retry policy (transport failures, 408, 429, 5xx retry; other non-2xx are final)
- Docs: http.md, broadcasting-pusher.md, broadcasting-mercure.md, webhook.md

### Out of Scope
- Changing `GuzzleHttpClient` exception messages
- Wiring `webhook.timeout` into the dispatcher

## Success Criteria
- [x] Mercure and Pusher send `http_errors => false`; 4xx/5xx throw `BroadcastException` whose context has the status and capped body
- [x] No message/context contains the signed Pusher query string
- [x] Status-aware suggestions for 401/403, 413, other 4xx, 5xx
- [x] Webhook dispatcher returns `successful: false` for 4xx/5xx; job records status + capped body, never records non-2xx as success, retries per policy
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | HttpResponse::bodyExcerpt | - | completed |
| 002 | BroadcastException::rejected | - | completed |
| 003 | MercureBroadcaster reads error responses | 001, 002 | completed |
| 004 | PusherBroadcaster reads error responses without leaking the signature | 001, 002 | completed |
| 005 | WebhookDispatcher returns error responses; WebhookResponse::isRetryable | - | completed |
| 006 | DispatchWebhookJob records and retries error responses | 001, 005 | completed |
| 007 | Docs | 001, 002, 003, 004, 005, 006 | completed |

## Architecture Notes
- The body cap lives on `HttpResponse` (marko/http) because all three senders already depend on that package; no new cross-package dependency.
- Transport failures (`HttpException`, including `ConnectionException`) are still caught; only HTTP error statuses move to the response branch.
- Shared formats are pinned in the task files: excerpt format (001), `rejected()` message/context/suggestions (002), `recordRejection()` signature (006). Every non-2xx response (3xx included) goes to `rejected()`.
- Tests for the `http_errors` paths must use `FakeHttpClient`, which honours the option. Hand-rolled clients do not, so tests built on them pass even without the fix.
- In `DispatchWebhookJob`, only `dispatch()` sits inside the try. Attempt recording happens outside it, so a repository failure never re-sends a delivered webhook.

## Risks & Mitigations
- Guzzle message formats vary: Pusher redaction strips any query string after the events path and replaces the raw signature value, instead of relying on an exact URL match.
- Behaviour change in DispatchWebhookJob (non-2xx no longer recorded as success): documented in webhook.md.
