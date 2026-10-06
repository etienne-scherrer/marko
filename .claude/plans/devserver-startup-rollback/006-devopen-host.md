# Task 006: dev:open uses the recorded host

**Status**: completed
**Depends on**: 001, 005
**Retry count**: 0

## Description
Record the PHP server's host in the PID file and have `dev:open` open it (bracketed for IPv6, `localhost` for wildcard addresses), falling back to `localhost` for PID files written before the host was recorded.

## Context
- Related files: packages/devserver/src/Process/ProcessEntry.php, packages/devserver/src/Process/PidFile.php, packages/devserver/src/Command/DevOpenCommand.php, packages/devserver/src/Command/DevUpCommand.php

- Depends on 005 because 004, 005 and 006 all edit `DevUpCommand.php`.
- Contract: add `public ?string $host = null` as the last `ProcessEntry` parameter. `PidFile::write()` writes `host` and `read()` uses `$p['host'] ?? null`. `DevUpCommand` records `$serverHost->address` on the `php` entry. `DevOpenCommand` builds `"http://" . ServerHost::fromString($host ?? 'localhost')->forBrowser() . ":$port"`.

## Requirements (Test Descriptions)
- [x] `it stores and reads the host of a process entry`
- [x] `it reads entries written without a host`
- [x] `it opens the recorded host of the PHP server`
- [x] `it opens localhost for a wildcard host`
- [x] `it brackets an IPv6 host in the browser URL`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
