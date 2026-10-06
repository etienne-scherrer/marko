# Task 003: Update page-cache docs pages

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Fix the documented config key (`ttl` -> `default_ttl`) and getter (`ttl()` -> `defaultTtl()`), make the config examples match the shipped config, and document what a TTL of `0` means and that negative TTLs are rejected.

## Context
- Related files: `packages/docs-markdown/docs/packages/page-cache.md`, `packages/docs-markdown/docs/packages/page-cache-file.md`, `packages/page-cache/README.md`, `packages/page-cache-file/README.md`
- Patterns to follow: `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] `page-cache.md config example and table use default_ttl`
- [x] `page-cache.md documents ttl 0 / PAGE_CACHE_TTL=0 as never expires`
- [x] `page-cache.md lists negativeTtl exception and defaultTtl() getter`
- [x] `page-cache-file.md config example uses default_ttl and explains null expiry`

## Acceptance Criteria
- Docs match shipped behaviour and config
- Describe failure timing accurately: a negative `#[Cacheable]` ttl throws when routes are discovered (application boot); a negative `default_ttl` throws on the first cache store (first miss on a cacheable route). Do not say "on first request" for the attribute case.
- Also fix the config-class API listing in `page-cache.md` (`ttl(): int` -> `defaultTtl(): int`) and the config example to use `$_ENV['PAGE_CACHE_TTL'] ?? 3600` matching `packages/page-cache/config/page-cache.php`

## Implementation Notes
(Left blank - filled in by programmer during implementation)
