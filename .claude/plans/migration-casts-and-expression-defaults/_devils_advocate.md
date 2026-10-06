# Devil's Advocate Review: migration-casts-and-expression-defaults

## Critical (Must fix before building)
- **003**: Dropping/restoring the default around a type change breaks auto-increment columns. SERIAL defaults are `nextval(...)`, and the target default for an auto-increment column is `null`, so an int -> bigint change on `id` would leave the sequence detached. Fix: never drop or restore the default when the column is `autoIncrement`. Added a unit test to 003 and an integration test to 007.

## Important (Should fix before building)
- **002/003**: `Column::defaultEquals()` is `private` and asymmetric (an entity `null` accepts any default). `PgSqlGenerator` (line ~497) compares defaults with `!==`. Two distinct `Expression` instances are never `!==`-equal, so every diff would emit a spurious SET DEFAULT. Fix: 002 adds a public, symmetric `Column::hasSameDefaultAs()`, and 003 now depends on 002 and uses it.
- **003/007**: The case where the target column has no default (drop without restoring) had no test. There was also no integration test for a default that can't be cast implicitly. Both added.

## Minor (Nice to address)
- 001: The zero-argument shortcut regex matches any identifier with `()`, including user strings like `"todo()"`. This is documented via `Literal`, but call it out in the docs.
- 006: The behavior for MariaDB/5.7 servers that don't report `DEFAULT_GENERATED` depends on the keyword list. Make sure `LOCALTIMESTAMP` is covered too.

## Questions for the Team
- Should `USING` be emitted only for casts with no implicit assignment cast? Emitting it on every type change is safe but noisier in generated migrations.
