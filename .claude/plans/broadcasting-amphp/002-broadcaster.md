# Task 002: AmphpBroadcaster

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Publish broadcasts through PublisherInterface as JSON messages.

## Requirements (Test Descriptions)
- [x] `it publishes to the prefixed channel`
- [x] `it publishes private channels under the private- prefix`
- [x] `it encodes event data and id as JSON`
- [x] `it generates a sortable id when none is given`
- [x] `it rejects public channel names that start with private-`
- [x] `it throws when the payload exceeds the PostgreSQL NOTIFY limit`
- [x] `it dispatches a broadcastable to each channel`
- [x] `it rejects event names and ids containing CR or LF` (they would inject SSE frames)
- [x] `it allows payloads over 8000 bytes on non-PostgreSQL publishers`
- [x] `it wraps publisher failures in BroadcastException::publishFailed`

## Implementation Notes
- Constructor: `PublisherInterface`, `AmphpBroadcastingConfig`. Follow `MercureBroadcaster` for `Channel::from`, empty event check and JSON flags.
- Message payload is `json_encode(['event' => ..., 'data' => ..., 'id' => ...])`; pubsub channel per `_plan.md` Shared contracts.
- The 8000-byte guard applies only when the publisher is `Marko\PubSub\PgSql\Driver\PgSqlPublisher` (`instanceof` on a class from an uninstalled package is safe; do not import-require it). Redis users must not be capped.

## Acceptance Criteria
- All requirements have passing tests
