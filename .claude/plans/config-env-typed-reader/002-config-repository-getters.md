# Task 002: Tighten ConfigRepository::getBool() and getInt()

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Defence in depth: `getBool()` accepts real bools, ints 0/1 and the `Env::TRUE_VALUES`/`Env::FALSE_VALUES` token set (trimmed, case-insensitive), throwing otherwise; `getInt()` accepts ints and strings passing `FILTER_VALIDATE_INT`. Reuse the `Env` constants from task 001; do not duplicate the token list. Apply the same rules to `Marko\Testing\Fake\FakeConfigRepository`, which currently uses `(bool)`/`(int)` casts, so tests using the fake behave like production.

## Context
- Related files: packages/config/src/ConfigRepository.php, packages/config/tests/Unit/ConfigRepositoryTest.php, packages/config/src/Env.php (constants from 001), packages/testing/src/Fake/FakeConfigRepository.php, packages/testing/tests/Unit/Fake/FakeConfigRepositoryTest.php

## Requirements (Test Descriptions)
- [x] `it returns false from getBool for 'off', 'no', 'false' and '0'`
- [x] `it throws from getBool for an unrecognised string such as 'ture'`
- [x] `it throws from getInt for '1.5', '1e3' and a float`
- [x] `it returns an int from getInt for an integer string`
- [x] `FakeConfigRepository getBool and getInt apply the same rules as ConfigRepository`

## Acceptance Criteria
- All requirements have passing tests; full suite still green (fix any call site or test that relied on lax casting, outside packages/database/src)

## Implementation Notes
