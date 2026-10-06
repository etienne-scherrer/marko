# Task 002: mail-smtp connect and STARTTLS reasons

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`StreamSocket::connect()` drops `$errstr` and suppresses the warning; `enableTls()` lets a raw warning through and `tlsFailed()` has no reason. Capture both and carry the OS reason into `TransportException`.

## Context
- Related files: packages/mail-smtp/src/StreamSocket.php, packages/mail/src/Exception/TransportException.php, packages/mail-smtp/tests/Unit/StreamSocketTest.php, packages/mail/tests/Unit/Exception/TransportExceptionTest.php
- Decision: `StreamSocket::enableTls()` throws `TransportException::tlsFailed($host, $reason)` itself on handshake failure (it knows the host from `connect()`); it still returns `false` when not connected, so `SmtpTransport::startTls()`'s check stays for other `SocketInterface` implementations.

- Host tracking: add `protected ?string $host = null`, set in `connect()`, cleared in `close()`. `TestableStreamSocket::injectStream()` (StreamSocketTest.php:14) sets a stream without `connect()`, so `enableTls()` must fall back to a placeholder (e.g. `'unknown host'`) when `$host` is null.
- Connect reason: wrap `stream_socket_client()` in `ErrorCapture::run()`; use `$errstr` when non-empty, otherwise the captured warning (DNS/pre-connect failures leave `$errstr` empty). Test against a guaranteed-closed port: bind `tcp://127.0.0.1:0`, read the port, `fclose()` the server, then connect.
- STARTTLS failure test recipe (avoid a 60s handshake hang): `stream_socket_server('tcp://127.0.0.1:0')`, `connect()`, `stream_socket_accept()` the peer, `fwrite` a plain-text line (e.g. `"220 not tls\r\n"`) and `fclose` the peer, then `enableTls()`. Assert TransportException, a non-null reason in the context, and no error-handler call (record every call, do not filter by `error_reporting()`).
- `connectionFailed`/`tlsFailed` reason format: append `": $reason"` to the context when non-null (same as `DiscoveryCacheException::notWritable()`).

## Requirements (Test Descriptions)
- [x] `it appends the reason to the connectionFailed context when one is given`
- [x] `it keeps the connectionFailed context unchanged without a reason`
- [x] `it appends the reason to the tlsFailed context when one is given`
- [x] `it throws a loud TransportException with the OS reason when the connection cannot be established`
- [x] `it throws tlsFailed with the handshake reason and raises no PHP warning when STARTTLS fails`

## Acceptance Criteria
- All requirements have passing tests
- No `@` in StreamSocket

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
