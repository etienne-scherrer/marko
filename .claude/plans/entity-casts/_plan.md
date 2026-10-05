# Plan: Entity Casts, Timestamps and Encrypted Columns

## Created
2026-10-05

## Status
completed

## Objective
Give `marko/database` entities a single, extensible value-conversion pipeline: custom `#[Cast]` classes, timezone-correct datetimes, automatic `#[Timestamps]`, and `#[Encrypted]` columns backed by `marko/encryption`.

## Related Issues
Closes #178

## Discovery Notes
- Reads go through `EntityHydrator::convertToPhpType()`; inserts through `EntityHydrator::convertToDbValue()`; updates through a second private copy `Repository::convertToDbValue()` that does not JSON-encode (so updating a json column binds a raw PHP array — a real bug the single pipeline fixes).
- `EntityHydrator` is constructed with `new EntityHydrator()` in ~26 test files and by the container, so new constructor params must be optional.
- `EntityMetadataFactory` is a container singleton built with no args. The container autowires nullable class params (it always resolves them), `ContainerInterface` is registered as an instance, and `Container::has()` returns false for an unbound interface — usable for the "no encryptor bound" check without instantiating the encryptor.
- `DatabaseConfig` requires `host`/`port`/etc. and is not valid for the `readwrite` driver, so the timezone is read through a small dedicated `DatabaseTimezoneConfig` (optional `timezone` key in `config/database.php`, default `UTC`).
- No clock package exists yet (#182 not merged) — timestamps use a protected `Repository::now()` seam.
- CHANGELOG.md is generated from PR titles/labels by `bin/release.sh`; the behaviour change is documented in the docs page and the PR body instead.

## Scope

### In Scope
- Single conversion pipeline (`EntityHydrator::toDatabaseValue()` / `toPhpValue()`), used by insert, batch insert and update
- `CastInterface`, optional `EquatableCastInterface`, `CastResolver` (container-backed), built-in Json/Enum/DateTime/Scalar casts
- `#[Cast]` attribute, cast-aware dirty checking
- Timezone-correct datetime read/write (`database.timezone`, default UTC)
- `#[Timestamps]` entity attribute (insert sets both unless explicitly set, update sets `updatedAt` when something is dirty)
- `#[Encrypted]` attribute (cast on top of the property's normal conversion), text column in schema, clear errors when no encryptor
- Docs: database.md, encryption.md link, README unchanged pointer check

### Out of Scope
- Clock injection (#182) — seam only
- Changing inferred schema types for existing properties (e.g. DateTimeImmutable still infers varchar without `type:`)
- Querying encrypted columns by value

## Success Criteria
- [ ] Insert and update produce identical DB values for every built-in type
- [ ] Custom cast round-trips; unchanged value objects are not dirty
- [ ] America/New_York datetime stored as UTC instant and read back as the same instant
- [ ] Timestamps behaviour per ticket
- [ ] Encrypted stores ciphertext, hydrates plaintext, clear error when no encryptor
- [ ] Schema types for cast and encrypted properties
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Single conversion pipeline refactor | - | completed |
| 002 | Cast contracts, resolver and built-in casts | 001 | completed |
| 003 | Timezone-correct datetimes | 002 | completed |
| 004 | #[Cast] attribute and cast-aware dirty checking | 002 | completed |
| 005 | #[Timestamps] | 004 | completed |
| 006 | #[Encrypted] columns and schema types | 004 | completed |
| 007 | Documentation | 003, 004, 005, 006 | completed |

## Interface Contracts (shared by all tasks)
Existing scaffolding in the worktree IS the contract — do not redefine: `Entity/Cast/CastInterface.php` (`toPhp(mixed $value, PropertyMetadata $meta): mixed`, `toDatabase(mixed $value, PropertyMetadata $meta): mixed`), `Entity/Cast/EquatableCastInterface.php` (`equals(mixed $a, mixed $b, PropertyMetadata $meta): bool`), `JsonCast`, `EnumCast`, `ScalarCast`, `Config/DatabaseTimezoneConfig.php` (`fromName()` for tests). Task 002 adds `DateTimeCast` and `CastResolver`.

- `EntityHydrator::toDatabaseValue(mixed $value, PropertyMetadata $meta): mixed` and `EntityHydrator::toPhpValue(mixed $value, PropertyMetadata $meta): mixed` (task 001) — public; null in → null out; used by `extract()`, `hydrate()` (parent AND extender branch), `Repository::update()` (parent + companions, using `$metadata->properties[$name]`), and criteria binding.
- `EntityHydrator::__construct(?EntityMetadataFactory $metadataFactory = null, ?CastResolver $castResolver = null)` (task 002); null resolver → internal `new CastResolver()`.
- `CastResolver::__construct(?ContainerInterface $container = null)`; `CastResolver::resolve(string $castClass): CastInterface` (cached per class) (task 002).
- Conversion precedence (task 002/004): `castClass` (explicit #[Cast]) > `columnType === 'json'` → JsonCast > `enumClass` → EnumCast > `type === DateTimeImmutable::class` → DateTimeCast > ScalarCast. Encryption (task 006) wraps the result: write = cast → `(string)` → encrypt; read = decrypt → cast.
- `PropertyMetadata` new trailing optional params: `?string $castClass = null` (004), `bool $encrypted = false` (006). `EntityMetadata` new trailing optional param `?TimestampsMetadata $timestamps = null` (or `?string $createdAtProperty`, `?string $updatedAtProperty`) (005). Use named args everywhere — existing positional callers must keep working.
- Dirty snapshot (004): `getOriginalValues()` keeps returning PHP values (public API, asserted by existing tests). A second `WeakMap` stores the pre-encryption database representation for cast/encrypted properties only, captured in `hydrate()` and `registerOriginalValues()`; dirty checks for those properties compare `EquatableCastInterface::equals()` when available, otherwise that database representation.

## Architecture Notes
- Null short-circuits in the hydrator both ways; casts never see null.
- Encryption is layered: write = cast->toDatabase → string → encrypt; read = decrypt → cast->toPhp. Dirty checking compares the unencrypted representation.
- Cast instances are resolved via `ContainerInterface::get()` (Preferences apply); without a container only no-arg casts can be built, otherwise a loud `EntityException`.

## Risks & Mitigations
- Conflicts with #159/#176 in Repository/EntityHydrator: keep edits local to conversion/insert/update code.
- Behaviour change for non-UTC PHP default timezone: documented, configurable via `database.timezone`.
