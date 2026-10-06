# Task 001: Marko\Config\Env typed reader

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Config\Env`, a static, explicitly imported reader for config files. Config files run inside `ConfigLoader` before the container exists, so it must work without DI.

## Context
- Related files: packages/config/src/Env.php (new), packages/config/src/Exceptions/ConfigException.php, packages/env/src/functions.php (lookup order to mirror)
- Patterns to follow: MarkoException message/context/suggestion shape

## Requirements (Test Descriptions)
- [x] `it reads $_ENV first, then getenv()`
- [x] `it returns the default when the variable is unset or an empty string`
- [x] `it parses int values and rejects 'abc', '10s', '1.5' and '1e3'`
- [x] `it enforces optional min and max for int and float`
- [x] `it parses bool tokens true/false/1/0/yes/no/on/off case-insensitively and rejects 'ture'`
- [x] `it parses float, string, nullableString and comma-separated list values`
- [x] `it throws ConfigException naming the variable, the value and the accepted forms`
- [x] `it returns null from nullableInt when unset or empty and validates it like int otherwise`
- [x] `it trims list items, drops empty items and returns a reindexed list`
- [x] `it maps a bool $_ENV value to true/false and ignores non-scalar $_ENV values in favour of getenv()`

## Interface Contract (tasks 002, 004, 005 build against this exactly)
```php
class Env
{
    /** @var list<string> */ public const array TRUE_VALUES = ['true', '1', 'yes', 'on'];
    /** @var list<string> */ public const array FALSE_VALUES = ['false', '0', 'no', 'off'];

    public static function string(string $name, string $default = ''): string;
    public static function nullableString(string $name, ?string $default = null): ?string;
    public static function int(string $name, int $default, ?int $min = null, ?int $max = null): int;
    public static function nullableInt(string $name, ?int $default = null, ?int $min = null, ?int $max = null): ?int;
    public static function float(string $name, float $default, ?float $min = null, ?float $max = null): float;
    public static function bool(string $name, bool $default): bool;
    /** @param list<string> $default  @return list<string> */
    public static function list(string $name, array $default = []): array;
}
```
- Lookup: `$_ENV[$name]` if it exists and is scalar (bool -> `'true'`/`'false'`, other scalars stringified), else `getenv($name)`; `false` from getenv = unset.
- Unset or `''` (after trim for typed readers) returns the default unchanged; defaults are NOT validated against min/max.
- `string`/`nullableString` return the raw value (no trim).
- `int`/`nullableInt`: `filter_var(trim($v), FILTER_VALIDATE_INT)`; rejects `'abc'`, `'10s'`, `'1.5'`, `'1e3'` and overflow.
- `float`: trimmed value must be `is_numeric` and finite; rejects `'abc'`, `'inf'`, `'nan'`.
- `bool`: trimmed and lowercased value must be in `TRUE_VALUES`/`FALSE_VALUES`.
- `list`: split on `,`, trim each item, drop `''` items, `array_values`.
- min/max violations throw a `ConfigException` naming the bound.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- Tests snapshot and restore every `$_ENV` key and `putenv` variable they touch (the suite runs `--parallel`)
- Test file: packages/config/tests/Unit/EnvTest.php

## Implementation Notes
