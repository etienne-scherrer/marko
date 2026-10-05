# Task 001: NUL-safe base64 JobEnvelope with legacy support

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Change `JobEnvelope::wrap()` to emit `{hmac}.b64:{base64(serialized)}` so payloads are 7-bit safe in PostgreSQL `TEXT`. `verifyAndUnwrap()` accepts both the new format and the legacy raw format.

## Context
- Related files: packages/queue/src/JobEnvelope.php, packages/queue/tests/JobEnvelopeTest.php, packages/queue/src/Exceptions/SerializationException.php
- HMAC covers the full segment after the separator.

## Requirements (Test Descriptions)
- [x] `it wraps payloads in the b64 envelope format`
- [x] `it produces envelopes without NUL bytes for objects with private and protected properties`
- [x] `it round-trips a new-format envelope back to the original serialized bytes`
- [x] `it still verifies and unwraps legacy raw envelopes`
- [x] `it rejects a new-format envelope whose base64 was tampered with`
- [x] `it rejects a new-format envelope whose b64 body is not valid base64 even with a valid signature`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
