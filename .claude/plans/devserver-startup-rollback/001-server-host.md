# Task 001: ServerHost value object

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\DevServer\Process\ServerHost`, which validates the `--host`/`dev.host` value and normalizes it once: one pair of surrounding brackets is accepted for IPv6 literals, the stored address is unbracketed, and helpers format it for a URI and for a browser.

## Context
- Related files: packages/devserver/src/Process/ServerHost.php (new), packages/devserver/src/Process/ProcessManager.php (`hostForUri()`)
- Validation replaces the character-class regex in DevUpCommand: IPv4/IPv6 via `filter_var(FILTER_VALIDATE_IP)`, otherwise an RFC 1123 hostname pattern.

## Requirements (Test Descriptions)
- [x] `it accepts hostnames and IPv4 addresses unchanged`
- [x] `it accepts a bare or bracketed IPv6 address and stores it unbracketed`
- [x] `it rejects values that are not a hostname or IP address`
- [x] `it brackets IPv6 addresses for a URI and leaves other hosts alone`
- [x] `it substitutes localhost for wildcard addresses in a browser URL`

## Acceptance Criteria
- All requirements have passing tests
- ProcessManager reuses the URI formatting instead of its own copy

## Implementation Notes
