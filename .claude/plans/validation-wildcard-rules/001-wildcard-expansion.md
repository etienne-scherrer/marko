# Task 001: Wildcard expansion in Validator

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Expand rules keys containing `*` against the data into concrete keys before validating, key errors by the concrete path, and fail loudly when a wildcard segment meets a scalar. Extract the dot-path lookup into a shared `DataPath` class.

## Context
- Related files: packages/validation/src/Validation/Validator.php, new packages/validation/src/Validation/DataPath.php
- Patterns to follow: existing ValidatorTest (Pest `it()` closures), TestUploads support class
- Already present in the worktree: `packages/validation/tests/Unit/Validation/WildcardRulesTest.php` (pre-written specs) and `src/Validation/DataPath.php`. Build against them; do not recreate them.

## Requirements (Test Descriptions)
- [x] `it validates every item of photos.* and keys errors by index`
- [x] `it no longer silently passes invalid items under a wildcard rule`
- [x] `it expands a nested items.*.name key`
- [x] `it expands multiple wildcards in matrix.*.*`
- [x] `it produces no errors for a wildcard over an absent or empty parent`
- [x] `it still fails a required array parent when it is absent`
- [x] `it fails loudly when a wildcard segment meets a scalar`
- [x] `it reports a scalar at a wildcard position under every key that reaches it`
- [x] `it treats a leading wildcard as every top-level key`
- [x] `it fails loudly when a wildcard segment meets a single uploaded file` (e.g. `photos` holds one `UploadedFile` instead of a list: any non-array, non-empty value, objects included, gets the "must be an array" error)
- [x] `it throws for an unknown rule under a wildcard key even when the parent is empty` (rules are parsed before expansion)
- [x] `it resolves dot paths with DataPath`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`DataPath::get()` replaces `Validator::getValue()`. Expansion lives in `Validator::expand()`. Any non-array, non-empty value at a `*` adds "The {path} field must be an array to apply the {key} rules." under the path reached. That is one message per (path, rule key) pair: two rule keys that reach the same scalar each add their own message (see the pre-written test).

- Parse each rule key's rules with `RuleParser` once, **before** expanding, so typos and missing parameters throw even when the wildcard matches nothing. Reuse the parsed rules for every concrete key.
- Contract for Task 002: `expand()` returns `array<string, list<int|string>>`, mapping each concrete key to the wildcard indexes it matched, in order (`items.*.name` → `['items.0.name' => [0], ...]`; plain keys → `[key => []]`). Indexes may be string keys when the data is associative.
