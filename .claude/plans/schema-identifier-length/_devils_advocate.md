# Devil's Advocate Review: schema-identifier-length

## Critical (Must fix before building)

- **Task 003: `buildIndex()` / `indexNameTooLong()` contract not pinned.** The plan says "`buildIndex()` takes the entity class" but not the parameter order, whether it is required, or the factory signature. `SchemaBuilder::buildIndex()` is public and is called from `SchemaBuilder::build()` (line 42) and `SchemaRegistry::registerEntities()` (line 165, where `$extenderClass` is in scope). Fix: pin `buildIndex(IndexMetadata $metadata, string $entityClass): Index` (required, `class-string`), `build()` passes `$metadata->entityClass`, and `EntityException::indexNameTooLong(string $entityClass, string $indexName, int $byteLength): self`.

## Important (Should fix before building)

- **Task 001 is already implemented in the worktree** (`packages/database/src/Schema/IdentifierName.php` plus its test, uncommitted). Its requirements don't match the code: "exactly 63 bytes" is wrong because `rtrim('_')` and `mb_strcut` can return fewer bytes. Fix: record the real contract (`derive(string $body, string $prefix = '', string $suffix = '')`, `fits()`, `byteLength()`, `MAX_BYTES`) so tasks 002 to 006 build against it, and align the requirements with the tests.
- **Task 001/004: no golden-value regression test.** If the hash scheme changes later (for example a different cut or algorithm), every shortened index and FK in production gets renamed, and nothing would catch it because the tests compute the expected value with `hash()`. Fix: add a literal expected-name test.
- **Task 004: derived `_unique` names only appear in ALTER diffs.** `deriveUniqueColumnIndexes()` skips columns being added, and CREATE TABLE/ADD COLUMN declare UNIQUE inline. A worker who feeds a create-table diff will never see the shortened name. Fix: spell out that the database table must already have the column as non-unique.
- **Task 005: on PostgreSQL the test passes even without the fix.** PG truncates with a NOTICE, and both the derived unique index and the FK are matched by column, not name, so the "diff is empty" assertion alone proves nothing. Fix: assert that the introspected index/FK names equal `IdentifierName::derive(...)` output. Also: add the new table to `dropTables` (child before parent, since MySQL has no CASCADE), and name the FK target (`settle_users.id`).
- **Task 006: upgrade impact undocumented.** Apps on PostgreSQL that already have a declared `#[Index]` name over 63 bytes (silently truncated today) will start throwing on db:diff/db:migrate. Renaming the index causes one drop+create of the truncated index. Docs need an upgrade note.

## Minor (Nice to address)

- `IdentifierName::derive()`: if `strlen($prefix) + strlen($suffix) + 9 > 63`, `$room` goes negative, and `mb_strcut` with a negative length cuts from the end, which silently produces a wrong name. No current caller hits this, but a guard (throw) would be loud.
- DiffCalculator docblocks (`deriveUniqueColumnIndexes`, `foreignKeyReplacementIndexes`) and the `SchemaBuilder::buildForeignKeys` comment still describe the raw `<table>_<column>_unique` form. Update them to mention shortening.
- `foreignKeyReplacementIndexes()` `_index` path has no real-DB coverage (only unit). Acceptable.

## Questions for the Team

- Validation location: the plan validates in `SchemaBuilder::buildIndex()`, so the error only fires on schema paths (db:diff/db:migrate). Validating in `EntityMetadataFactory::parse()` (next to `encryptedUniqueOrIndexed`) would avoid the public signature change and fail earlier, but would also break runtime hydration for any entity with an over-long name. The plan keeps `buildIndex()`; confirm.
- `buildIndex()` gains a required parameter, which breaks BC for any third-party caller. No in-repo callers besides the two above. OK pre-1.0?
