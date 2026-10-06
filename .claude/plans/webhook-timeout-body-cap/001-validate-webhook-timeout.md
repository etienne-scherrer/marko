# Task 001: Validate webhook.timeout in WebhookConfig

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`WebhookConfig` must reject a non-positive `webhook.timeout` with a `ConfigException` naming the key, because `0` means "no timeout" in Guzzle.

## Context
- Related files: packages/webhook/src/Config/WebhookConfig.php, new packages/webhook/tests/Config/WebhookConfigTest.php
- Patterns to follow: `TokenConfig::expirationDays()` ConfigException(message, context, suggestion)

## Requirements (Test Descriptions)
- [x] `it reads every webhook config value`
- [x] `it throws a config exception naming webhook.timeout when the timeout is not positive` (dataset 0, -1)
- [x] `it throws ConfigNotFoundException when webhook.timeout is missing`

## Details
- Throw `Marko\Config\Exceptions\ConfigException` with: message `Configuration key "webhook.timeout" must be a positive integer`, context `sprintf('Got %s', var_export($timeout, true))`, suggestion explaining it is the number of seconds an outgoing webhook request may take before it is abandoned and retried (e.g. 30), and that 0 would wait forever.
- Update the constructor docblock to `@throws ConfigException|ConfigNotFoundException`.
- Validate only `timeout`; leave other keys and `config/webhook.php` untouched (#289).
- `WebhookReceiver` also constructs `WebhookConfig`; existing receiver tests use `timeout => 30` and must keep passing.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
