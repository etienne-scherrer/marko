# Task 004: DevUpCommand IPv6 host handling

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Normalize the host with `ServerHost` at the top of `execute()`, pass the unbracketed address to `isPortAvailable()`/`waitUntilAccepting()`, and start `php -S [::1]:PORT` for IPv6.

## Context
- Related files: packages/devserver/src/Command/DevUpCommand.php, packages/devserver/tests/Command/DevUpCommandTest.php
- Depends on 003 for the `devserverListen()`/`devserverFreePort()` helpers in `tests/Helpers.php`, and for the IPv6 skip check already there.
- Contract: `$serverHost = ServerHost::fromString($host)` replaces the regex. `isPortAvailable()` and `waitUntilAccepting()` receive `$serverHost->address` (unbracketed, which `FakeProcessManager::$waitedFor` records). The PHP command and the "Starting PHP server" line use `$serverHost->forUri()`.
- Update the existing `Invalid host value` test (~line 473) to cover the new invalid cases. The `invalidHost()` message is unchanged.
- Real-server tests chdir into the temp project, and restore cwd and `stopAll()` in `finally`.

## Requirements (Test Descriptions)
- [x] `it brackets a bare or bracketed IPv6 host in the PHP server command`
- [x] `it passes the unbracketed IPv6 host to the port check`
- [x] `it rejects invalid host values` (bracketed hostname, shell metacharacters)
- [x] `it serves requests on the IPv6 loopback` (real server, skipped without IPv6 loopback)
- [x] `it binds all IPv6 interfaces for the :: host` (real server, skipped without IPv6 loopback)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
