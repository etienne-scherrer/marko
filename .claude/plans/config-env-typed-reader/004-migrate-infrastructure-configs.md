# Task 004: Migrate infrastructure config files to Env

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Rewrite the amphp, cache, cache-redis, cors, encryption, hashing, log, page-cache, pubsub, pubsub-pgsql, pubsub-redis, queue-rabbitmq, routing, sse and translation config files with `Env::*`, keeping every default exactly. Add `marko/config` to `marko/pubsub`.

## Context
- Related files: packages/{pkg}/config/{pkg}.php, packages/pubsub/composer.json

## Requirements (Test Descriptions)
- [x] `it fails config load when PAGE_CACHE_TTL is not an integer instead of producing never-expiring pages`
- [x] `it rejects a negative PAGE_CACHE_TTL at config load`
- [x] `it keeps every shipped default when no env variable is set`
- [x] `it reads SSE_MAX_CONNECTIONS as null when unset or empty`

## Acceptance Criteria
- Existing package tests still pass
- Tests live in each package's own tests dir and snapshot/restore every `$_ENV`/`putenv` variable they touch

## Implementation Notes (from review)
- Use the task 001 contract exactly. `sse.max_connections` -> `Env::nullableInt('SSE_MAX_CONNECTIONS', null, min: 1)`. Values defaulting to `null` (e.g. `REDIS_PASSWORD`, `PUBSUB_PGSQL_USER`) -> `Env::nullableString`.
- Lists (cors paths/origins/methods/headers/expose_headers, amphp channels) -> `Env::list` with an array default equal to today's parsed result (e.g. cors `paths` default `['*']`, `allowed_methods` default `['GET','POST','PUT','PATCH','DELETE','OPTIONS']`).
- `cors.supports_credentials` -> `Env::bool('CORS_SUPPORTS_CREDENTIALS', false)`.
- Bounds: ports `min: 1, max: 65535`; TTLs/counts/sizes `min: 0` only where 0 is meaningful; never use `min: 1` on keys where 0 means "disabled" or "no limit" (`cors.max_age`). Read the consuming class before adding a bound. When unsure, use `min: 0`.
- Keep every default literal byte-for-byte (including `10 * 1024 * 1024`).
- Add `"marko/config": "self.version"` to packages/pubsub/composer.json `require`.
