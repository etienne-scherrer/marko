# Task 004: Docs pages and READMEs

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Replace the manual-binding instructions in the cache-redis, pubsub-redis and queue-rabbitmq docs pages with the shipped config files, including config key and env var tables, and keep the READMEs as slim pointers.

## Context
- Related files: packages/docs-markdown/docs/packages/{cache-redis,pubsub-redis,queue-rabbitmq}.md, package READMEs
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `cache-redis docs document config/cache-redis.php keys and env vars`
- [x] `pubsub-redis docs document the prefix source of truth`
- [x] `queue-rabbitmq docs document config/queue-rabbitmq.php keys and env vars`

## Acceptance Criteria
- Docs reflect actual behavior; no manual-binding workaround remains

## Implementation Notes
Docs-only task; verified by reading against the code.
