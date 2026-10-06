# Devil's Advocate Review: database-session-timezone

## Critical (Must fix before building)
- **001/002/003 contract undefined.** Tasks 002 and 003 build in parallel against `DatabaseConfig::$timezone` and `fixedTimezoneOffset()` with no type or return shape. Fixed: `public string $timezone` (canonical `DateTimeZone::getName()`), `public function fixedTimezoneOffset(): ?string` returning `'+HH:MM'`/`'-HH:MM'` or null; UTC returns `'+00:00'`.
- **001: `DatabaseTimezoneConfig::resolveTimezone()` is `private static`.** "Reuse it" is impossible as written. Fixed: make it `public static` (it takes `mixed`, unlike `fromName(string)`, which would TypeError on a non-string config value).
- **001: `fromArray()` sets properties through an explicit `$props` list on a `readonly` class.** If `timezone` is missing from that list, any read throws "must not be accessed before initialization". Added a requirement for this.
- **004: `ConfigRepositoryInterface::get()` throws `ConfigNotFoundException` when the key is missing.** It has no default. Fixed: guard with `has('database.timezone')`.

## Important (Should fix before building)
- **003: `PgSqlConnection::connect()` assigns `$this->pdo` before running the session statements.** If `SET TIME ZONE` fails, `$this->pdo` stays set, so the next `connect()` returns early and the app silently runs in the server zone. Fixed: assign only after the session setup succeeds, and test this.
- **003: the catch-all turns every PDOException into `connectionFailed`.** Fixed: run `SET TIME ZONE` in its own try so a rejection maps to `unknownTimezone`.
- **002: the 1298 detection was unspecified, and the list of fakes was vague.** Fixed: check the code (`errorInfo[1]` or `getCode()`), list the SQLite fakes that forward options, and add a test that other PDOExceptions still raise `connectionFailed`.
- **001/002: "UTC" is not fixed under the `getLocation() === false` rule** (PHP returns a location for UTC). Fixed: the rule is now stated explicitly.
- **004: per-node `timezone` precedence was undefined.** Fixed: the top-level value always wins.

## Minor (Nice to address)
- MySQL rejects offsets outside -13:59..+14:00. PHP accepts wider offsets, which will surface as a connection error.
- PgBouncer in transaction-pooling mode drops session `SET`. This is worth a docs note.

## Questions for the Team
- Should `DatabaseTimezoneConfig` and `DatabaseConfig::$timezone` share one source of truth long-term, since the file is currently read twice?
