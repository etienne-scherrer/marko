# Task 004: RateLimit attribute, RateLimiterConfig, key resolver

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `#[RateLimit(maxAttempts, decaySeconds, name)]` (class|method), `RateLimiterConfig` reading `ratelimiter.default_max_attempts` / `default_decay_seconds`, and `RateLimitKeyResolverInterface` with the default `ClientIpKeyResolver` (bound in module.php).

## Context
- Related files: packages/ratelimiter/src/Attributes/RateLimit.php, src/Config/RateLimiterConfig.php, src/Contracts/RateLimitKeyResolverInterface.php, src/ClientIpKeyResolver.php, config/ratelimiter.php, module.php
- Patterns: packages/cache/src/Config/CacheConfig.php

## Requirements (Test Descriptions)
- [x] `it targets classes and methods`
- [x] `it rejects a non-positive maxAttempts or decaySeconds`
- [x] `it reads default limits from config`
- [x] `it ships default limits in the config file`
- [x] `it resolves the rate limit identity from the client ip`
- [x] `it binds RateLimitKeyResolverInterface to ClientIpKeyResolver`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
