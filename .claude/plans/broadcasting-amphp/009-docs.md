# Task 009: Docs page and README

**Status**: completed
**Depends on**: 002, 003, 007
**Retry count**: 0

## Description
`docs/packages/broadcasting-amphp.md`, slim README, cross-links from broadcasting.md, sse.md and the real-time guide; nginx, RoadRunner, limits and replay notes.

Must also document: pubsub-redis opens one Redis connection per distinct active channel (size Redis `maxclients`), the per-stream channel cap, the pgsql 8000-byte payload limit, that a log driver is required, ext-pcntl for signals, raising `ulimit -n` for many connections, nginx `proxy_buffering off` / `proxy_read_timeout` above the heartbeat, `lastEventId` query fallback, and that tokens travel in the query string (keep it out of proxy access logs).

## Requirements (Test Descriptions)
- [x] `it has a README with installation, quick example and docs link`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS.md

## Implementation Notes
