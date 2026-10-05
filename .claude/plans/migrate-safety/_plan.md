# Plan: Migration Production Safety

## Created
2026-10-05

## Status
completed

## Objective
Make the database commands' production guards real (driven by core `AppEnvironment`), stop `db:migrate` from
auto-generating outside development, and stop the entity diff from dropping indexes the developer created by hand.

## Related Issues
Closes #170

## Discovery Notes
- `MigrateCommand`, `RebuildCommand`, `SeedCommand`, `SeederRunner` take `bool $isProduction = false` and nothing passes
  it, so every guard is dead. `RollbackCommand` and `ResetCommand` have the same dead `$isProduction` (not named in the
  ticket, same bug, fixed here too).
- Core `AppEnvironment` (#160) is registered as a container instance by `Application`; unset env = production. Using it
  directly in constructors (the ticket's preferred alternative) removes the bool entirely — no bindings needed for the
  commands; `SeederRunner`'s existing closure binding passes it.
- `DiffCalculator` drops every DB index the entity does not declare. `#[Index]` has no `where`. Database config is read
  from `config/database.php` by `DatabaseConfig` (no marko/config dependency), so the project-wide ignore list lives at
  `config/database.php` → `migrations.ignore_indexes`, i.e. the `database.migrations.ignore_indexes` key.
- Interactive confirmation helper exists only in `marko/devai`; the database package gets its own small prompter.
- Index diffing is name-based, so a partial index round-trips with an empty second diff as long as its name matches.

## Scope

### In Scope
- `AppEnvironment` replaces the bool in Migrate/Rebuild/Reset/Rollback/Seed commands and `SeederRunner`
- `db:migrate` generates only when `isDevelopment()`; `--generate` forces it, `--no-generate` suppresses it
- Drift warning outside development lists the differing SQL
- `#[Index(where: ...)]` partial indexes: pgsql generator + introspector; mysql generator throws
- `#[Table(unmanagedIndexes: [...])]` and `database.migrations.ignore_indexes` (names or glob patterns)
- Destructive statements (DROP COLUMN / DROP INDEX) listed before generation and require confirmation or `--force`
- Integration todos for #170 flipped to real tests
- Docs page + README

### Out of Scope
- Detecting changes to an existing index's definition (diff stays name-based)
- Expression indexes beyond `where`
- An unmanaged-columns list

## Success Criteria
- [x] `db:rebuild` and `db:seed` refuse and exit 1 with APP_ENV=production and with APP_ENV unset
- [x] `db:migrate` in production applies pending files, never generates, prints drift listing
- [x] `db:migrate` in local generates; `--no-generate` suppresses
- [x] Unmanaged / ignored indexes are not in the drop list
- [x] `#[Index(where:)]` round-trips on pgsql
- [x] Destructive statements are listed and require `--force` non-interactively
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Wire AppEnvironment into rebuild/reset/rollback/seed guards | - | completed |
| 002 | Partial index `where` in core schema model | - | completed |
| 003 | Unmanaged / ignored indexes in DiffCalculator | 001, 002 | completed |
| 004 | PgSql generator + introspector partial indexes | 002 | completed |
| 005 | MySql generator rejects partial indexes | 002 | completed |
| 006 | db:migrate environment gating, --generate, drift listing | 001 | completed |
| 007 | db:migrate destructive-change confirmation and --force | 003, 006 | completed |
| 008 | Integration tests for #170 | 003, 004, 006, 007 | completed |
| 009 | Docs page and README | 001-008 | completed |

## Architecture Notes
- Constructor-inject `AppEnvironment` (no bool, no closure bindings for commands).
- `DiffCalculator` gets `array $ignoredIndexes = []` (glob patterns via `fnmatch`), wired through a closure binding in
  `module.php` from `DatabaseConfig::$ignoreIndexes`.
- `Schema\Table` carries `unmanagedIndexes`; extenders' lists merge into the parent table.
- `ConfirmationPrompterInterface` + `StdinConfirmationPrompter` in `Marko\Database\Command`. The command writes the
  prompt via `Output`; the prompter only reads. Destructive = `SchemaDiff::hasDestructiveChanges()` (columns, indexes,
  foreign keys). Non-interactive refusal exits 1; user decline exits 0.
- New `db:migrate` options `generate` and `force` must be declared in `#[Command(flags: [...])]`.
- 001, 003 and 007 all edit module.php `bindings`; dependencies serialize them (001 -> 003 -> 007).
- Removing `isProduction` breaks existing tests that pass it by name; 001 and 006 update those call sites.

## Risks & Mitigations
- Parallel edits to `packages/database/` (#176, #177): keep changes surgical; rebase before PR.
- Postgres normalises `WHERE` expressions: diff is name-based, so no perpetual drift; documented that changing a
  `where` needs a new index name.
