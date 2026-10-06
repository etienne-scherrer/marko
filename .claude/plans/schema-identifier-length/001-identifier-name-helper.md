# Task 001: IdentifierName Helper

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Database\Schema\IdentifierName`, the single place that knows the 63-byte identifier limit and how to shorten a derived name to fit it.

## Context
- Related files: packages/database/src/Schema/IdentifierName.php (new), packages/database/tests/Schema/IdentifierNameTest.php (new)
- Patterns to follow: typed constants, static pure functions, strict types
- NOTE: a draft implementation and test already exist in this worktree (uncommitted). Verify them against this task rather than rewriting.

## Interface Contract (tasks 002-006 build against this)
```php
class IdentifierName
{
    public const int MAX_BYTES = 63;
    public static function derive(string $body, string $prefix = '', string $suffix = ''): string;
    public static function fits(string $name): bool;
    public static function byteLength(string $name): int;
}
```
- `derive()` returns `<prefix><body><suffix>` unchanged when it fits; otherwise `<prefix><cut body, trailing _ trimmed>_<crc32b of full untruncated name><suffix>`, at most 63 bytes. (It can be under 63 because of the multibyte cut and `_` trim.)
- Call sites: `derive("{$table}_{$column}", suffix: '_unique')`, `derive("{$table}_{$column}", suffix: '_index')`, `derive("{$table}_{$column}", prefix: 'fk_')`.

## Requirements (Test Descriptions)
- [x] `it returns a derived name that fits unchanged`
- [x] `it returns a derived name of exactly 63 bytes unchanged`
- [x] `it shortens a derived name over 63 bytes to at most 63 bytes keeping the prefix and suffix`
- [x] `it appends the crc32b hash of the full name`
- [x] `it derives a known literal name for a known long input` (golden value: hardcode the expected string, so a future change to the scheme, which would rename live indexes, fails this test)
- [x] `it derives the same shortened name for the same input`
- [x] `it derives different names for long inputs that share their first 63 bytes`
- [x] `it cuts a multibyte body without splitting a character`
- [x] `it measures identifier length in bytes`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Also added a guard: `derive()` throws `InvalidArgumentException` when the prefix and suffix leave no room for the body and hash.
