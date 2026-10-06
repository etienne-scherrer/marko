# Task 001: DatabaseTimezoneConfig format/parse helpers

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Give `DatabaseTimezoneConfig` two small public methods so every non-entity writer converts the same way `DateTimeCast` does: `format(DateTimeInterface): string` converts the instant to the database zone and formats it as `Y-m-d H:i:s`; `parse(string): DateTimeImmutable` reads a stored string as a time in the database zone.

## Context
- Related files: packages/database/src/Config/DatabaseTimezoneConfig.php, packages/database/src/Entity/Cast/DateTimeCast.php, packages/database/tests/Config/
- Patterns to follow: DateTimeCast::toDatabase()/toPhp()
- The class already has `private static function parse(mixed $name): DateTimeZone`; a same-named public instance method is a fatal redeclaration. Rename the private static to `resolveTimezone()` (update the constructor and `fromName()`).
- Implement `parse()` with `new DateTimeImmutable($value, $this->timezone)` (not a strict createFromFormat) so a driver returning an offset (e.g. PostgreSQL timestamptz `+00`) still yields the right instant.

## Requirements (Test Descriptions)
- [x] `it formats an instant in the database timezone whatever its own timezone`
- [x] `it formats in a non-UTC database timezone`
- [x] `it parses a stored string as a time in the database timezone`
- [x] `it round-trips an instant through format and parse`
- [x] `it throws when parsing a malformed stored string`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Added `format()` / `parse()` and a public `FORMAT` constant to `DatabaseTimezoneConfig`; renamed the private static `parse()` to `resolveTimezone()`. Tests in packages/database/tests/Config/DatabaseTimezoneConfigTest.php.
