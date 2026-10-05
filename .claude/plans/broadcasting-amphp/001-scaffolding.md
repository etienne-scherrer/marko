# Task 001: Package scaffolding, config, exception

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Create `packages/broadcasting-amphp` with composer.json, LICENSE, .gitattributes, config, module.php, `AmphpBroadcastingConfig` and `AmphpBroadcastException`.

## Context
- Patterns to follow: packages/broadcasting-mercure, packages/pubsub-pgsql/module.php

## Requirements (Test Descriptions)
- [x] `it binds BroadcasterInterface to AmphpBroadcaster`
- [x] `it builds AmphpBroadcastingConfig from the broadcasting-amphp config`
- [x] `it ships defaults for every config key`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
