# Task 002: RouteMatcher: static lookup, HEAD fallback, allowedMethods, bounded memo

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Rework `RouteMatcher` to use the static hash lookup and precedence order, fall back from HEAD to GET, report allowed methods for a path, and bound its memo.

## Context
- Related files: packages/routing/src/RouteMatcher.php, RouteMatcherInterface.php, tests/RouteMatcherTest.php
- Test doubles implementing RouteMatcherInterface in layout, page-cache, roadrunner tests must gain `allowedMethods()`: packages/roadrunner/tests/Worker/FakeRouteMatcher.php, packages/layout/tests/Unit/Middleware/LayoutMiddlewareTest.php (1), packages/page-cache/tests/Unit/CacheabilityCheckerTest.php (5), packages/page-cache/tests/Unit/Middleware/PageCacheMiddlewareTest.php (2). Doubles return `[]`.
- Interface contract (task 005 builds the `Allow` header from it): `allowedMethods(string $path): array<int, string>`. Path normalized like `match()`. Returns `[]` when no method matches the path. Otherwise: matched methods, plus `HEAD` when GET matches, plus `OPTIONS` always; de-duplicated, uppercase, ordered GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS, then any others alphabetically.
- Memo: hits only (dynamic routes; static routes use the hash lookup and are not memoized), cleared entirely when it reaches `RouteMatcher::MAX_MEMO_ENTRIES` (1,000).

## Requirements (Test Descriptions)
- [x] `it matches a static route over a dynamic route registered before it`
- [x] `it matches a more specific dynamic route regardless of registration order`
- [x] `it falls back to the GET route for HEAD when no HEAD route matches`
- [x] `it prefers an explicit HEAD route over the GET fallback`
- [x] `it returns the methods allowed for a path including HEAD and OPTIONS`
- [x] `it returns no allowed methods for an unknown path`
- [x] `it orders allowed methods canonically regardless of registration order`
- [x] `it does not memoize misses`
- [x] `it keeps the memo bounded after many unique paths`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
