# Task 001: Cookie Max-Age in marko/routing

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add an optional `?int $maxAge` (seconds) as the last `Cookie` constructor parameter, a `maxAge()` getter, and emit `Max-Age=` from `toSetCookieString()`.

## Context
- Related files: packages/routing/src/Http/Cookie.php, packages/routing/tests/Http/CookieTest.php
- Patterns to follow: existing attribute emission in `toSetCookieString()`

## Requirements (Test Descriptions)
- [x] `it returns the max age it was given`
- [x] `it defaults max age to null and emits no Max-Age attribute`
- [x] `it emits Max-Age after Expires in the Set-Cookie string`
- [x] `it emits Max-Age=0 for a max age of 0, which deletes the cookie`
- [x] `it emits Max-Age=0 for a negative max age`
- [x] `it does not derive an Expires attribute from max age`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Negative values are kept as given by `maxAge()` but emitted as `Max-Age=0` (RFC 6265 §4.1.1 syntax allows only non-negative digits; §5.2.2 treats `<= 0` as expire-now).
