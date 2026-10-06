# Plan: Schema Diff Uniqueness and MySQL Introspection Normalization

## Created
2026-10-06

## Status
completed

## Objective
Make uniqueness changes on existing columns generate real index SQL on MySQL and PostgreSQL, and stop MySQL's introspected
type names and string defaults from producing column diffs that never settle.

## Related Issues
Closes #295

## Discovery Notes
- `DiffCalculator::columnsEqual()` swallowed `unique` differences; `findIndexesToAdd()` only saw `#[Index]` metadata, so
  `#[Column(unique: true)]` on an existing column was never migrated. `findIndexesToDrop()` kept any single-column unique
  index on a unique column, and the MySQL introspector exposed a `$uniqueColumns` filter on `getIndexes()` (no longer used
  by `getTable()`, which already passes `[]`).
- PostgreSQL inline `UNIQUE` creates a constraint (`<table>_<column>_key`), which `DROP INDEX` cannot remove. `pg_indexes`
  already lists it; nothing says it is a constraint.
- `MySqlIntrospector` returned `DATA_TYPE` upper-cased (`INT`, `TINYINT`) and `COLUMN_DEFAULT` as a raw string, while entity
  types come from `EntityMetadataFactory::TYPE_MAP` (`integer`, `boolean`) and defaults are typed PHP values.
- Entities may also spell types with generator synonyms (`int`, `bool`, `string`). Once MySQL reports `integer`, an entity
  `int` would diff, so the entity side canonicalizes those synonyms (SchemaBuilder; SchemaRegistry duplicated SchemaBuilder's
  column/index building and now reuses it).
- `MigrationGenerator` wrote `alter_*` migrations with empty `up`/`down` when the diff and generator disagreed.

## Scope

### In Scope
- Derived unique indexes (`<table>_<column>_unique`) in the diff for unique non-PK columns, matched against existing
  single-column unique indexes by columns, so inline-created indexes never churn
- Column comparison in the diff ignores `unique` (Column::equals() stays strict)
- `Index::$constraint` flag set by the PostgreSQL introspector; PgSqlGenerator drops/re-adds constraints with
  `ALTER TABLE ... DROP/ADD CONSTRAINT`
- MySQL introspector: no hidden unique indexes, abstract type names, typed defaults, MariaDB default normalization
- Entity type synonym canonicalization in SchemaBuilder
- MigrationGenerator refuses empty alter migrations loudly
- Unit + integration tests, docs

### Out of Scope
- A MariaDB CI service (#297)
- MariaDB JSON-as-LONGTEXT type mapping
- Driver-specific synonyms beyond int/bool/string (e.g. `tinyint` on PostgreSQL)

## Success Criteria
- [x] Adding/removing `unique: true` generates index/constraint SQL on both drivers, both directions
- [x] A freshly created table with unique columns diffs empty on both drivers
- [x] MySQL integer/boolean/decimal/varchar/timestamp defaults diff empty against the generated table
- [x] MariaDB-shaped rows normalize (unit tests)
- [x] MigrationGenerator never writes an empty alter migration
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Index constraint flag and diff-owned uniqueness | - | completed |
| 002 | Entity type synonym canonicalization in SchemaBuilder | - | completed |
| 003 | MigrationGenerator refuses empty alter migrations | - | completed |
| 004 | PostgreSQL unique constraints in introspector and generator | 001 | completed |
| 005 | MySQL introspector: unhidden unique indexes, abstract types, typed defaults, MariaDB | 001 | completed |
| 006 | Integration tests on MySQL and PostgreSQL | 001, 002, 004, 005, 008 | completed |
| 007 | Docs: database.md, database-mysql.md, database-pgsql.md | 001, 004, 005 | completed |
| 008 | MySqlGenerator orders an FK column's replacement index around its unique index | 001 | completed |

## Architecture Notes
- The derived index lives only in the diff (DiffCalculator), not in the entity Table, so CREATE TABLE / ADD COLUMN keep
  their inline `UNIQUE` and no duplicate index is created. A column being added gets no derived index (its inline UNIQUE
  covers it).
- Normalization happens in introspectors and on the entity side (SchemaBuilder), never in Column::equals().
- `Index::$constraint` is driver metadata, ignored by Index::equals() (mirrors Column::$nativeType).
- Dropping uniqueness from an FK column: InnoDB will not drop the only index an FK uses (error 1553), so the diff adds a
  plain `<table>_<column>_index` replacement when no other index leads with that column, and MySqlGenerator adds it
  before the drop in up and restores the unique index before dropping it in down (task 008).
- `SqlGeneratorInterface::generateDropIndex()` keeps its name-only signature; PgSqlGenerator routes constraint drops
  internally.
- MigrationGenerator validates every alter diff before writing any file, so a refusal never leaves partial output.

## Risks & Mitigations
- Explicit `type: 'int'` entities would diff against MySQL `integer`: canonicalize entity synonyms in SchemaBuilder.
- Remaining driver/entity vocabulary gaps now raise from MigrationGenerator instead of writing empty files: the message
  names the table and the columns/indexes the diff reported, so the gap is reportable.
- MariaDB detection costs one `SELECT VERSION()` per getColumns(): negligible for a schema diff.
