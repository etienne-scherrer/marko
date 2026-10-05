# Devil's Advocate Review: entity-casts

## Critical (Must fix before building)

1. **No shared interface contract (001-006).** Tasks name `toDatabaseValue()`/`toPhpValue()`, `CastResolver`, `PropertyMetadata::$castClass`, an encrypted flag and timestamps metadata, but give no signatures. 003/004 run in parallel and 005/006 build on 004, so every worker would invent its own shape. Scaffolding already exists in the worktree (`Entity/Cast/CastInterface.php`, `EquatableCastInterface.php`, `JsonCast.php`, `EnumCast.php`, `ScalarCast.php`, `Config/DatabaseTimezoneConfig.php`), and task 002 calls these files "New". **Fix:** added an "Interface Contracts" section to `_plan.md` that pins the signatures, the new metadata fields, the conversion precedence and the dirty-snapshot design. It also marks the existing files as the contract.

2. **Dirty-check snapshot design is unspecified (004, 006).** `originalValues` stores PHP values by reference, and `getOriginalValues()` is public API (asserted in `EntityHydratorTest` and `RepositoryTest`). A mutable value object mutated in place compares `===` to itself, so the change is never seen. Encrypted values can't be compared at the ciphertext level because the IV is random. **Fix:** keep `getOriginalValues()` returning PHP values. Add a second WeakMap that holds the pre-encryption database representation, only for cast and encrypted properties. Update and companion code paths now use `PropertyMetadata`.

3. **The timestamps task would collide with in-flight work (005).** 005 depended only on 001, so it would run in parallel with 002 (hydrator rewrite) and 004 (`EntityMetadataFactory::parse`, `Repository::update`, dirty checking). All of these edit the same methods. **Fix:** 005 now depends on 004.

## Important (Should fix before building)

4. **Timestamp properties may be uninitialized (005).** `public DateTimeImmutable $createdAt;` has no default, and `extract()` calls `getValue()` on it, which throws `Error`. "Explicitly set" needs a definition (initialized and non-null). Timestamps must also be applied inside `insert()`/`insertBatch()`/`update()`, after the `EntityCreating`/`EntityUpdating` events, and only once per entity in `insertBatch` (rows are extracted twice). Other open points: a dirty companion should bump `updatedAt`; a user-modified `updatedAt` must not be overwritten; `#[Timestamps]` on an extender should throw. All added as requirements.
5. **The `Container::has()` check misses instance-registered encryptors (006).** `has()` only checks `bindings` and `class_exists`, so an encryptor registered via `$container->instance()` or a Preference counts as "not bound". The plan also didn't say what happens when the factory has no container (`new EntityMetadataFactory()`). **Fix:** spelled out the check, the factory constructor param, and the throw-without-container rule. Tests must use `bind()`.
6. **Decryption failures need context (006).** Existing plaintext rows, a rotated key or corrupt data would surface a bare `DecryptionException`. Added a requirement to wrap it with the entity, property and column.
7. **Encrypted columns with unique or index (006).** Ciphertext is non-deterministic, so a unique constraint means nothing, and MySQL rejects indexes on TEXT columns without a key length when you migrate. Added a parse-time rejection.
8. **`findBy`/`findOneBy`/`existsBy` criteria skip the pipeline (004, 006).** A value-object criterion binds an object and fails inside PDO. An encrypted criterion silently matches nothing, which breaks the "loud errors" principle. Added: criteria for mapped properties go through `toDatabaseValue()`, and criteria on encrypted properties throw.
9. **JSON validation blocks cast properties (004).** `jsonColumnTypeMismatch` throws when `type: 'json'` is used on a non-array property, so a JSON-backed value-object cast can't be declared. Added the exemption for cast properties.
10. **Casts on extender (companion) properties (004).** The hydrator's extender branch has its own conversion call. Added a requirement covering it.
11. **composer.json change was in the docs task (007 to 006).** Moved `suggest` (plus `require-dev`) for `marko/encryption` into 006, the task whose code needs it.

## Minor (Nice to address)

- `DateTimeCast` resolved through the container always autowires `?DatabaseTimezoneConfig` (the container never passes null for class params). The default-UTC fallback only applies without a container.
- Before this change, `array` properties without an explicit `type: 'json'` infer a json schema column, but the hydrator does not encode them (`columnType` is null). Consider choosing `JsonCast` when `type === 'array'` too.
- `JsonCast::toPhp()` declares `array`, so a scalar JSON document (`"123"`) raises a TypeError. This is pre-existing behavior.
- Timestamp columns infer `varchar` unless the user declares `type: 'datetime'`. The docs should say so explicitly.
- Encrypted nullable columns store plain NULL, which leaks whether a value is present. Document this.

## Questions for the Team

- Read path for datetimes: return values in the DB timezone (UTC), or convert them to `date_default_timezone_get()` after parsing?
- `#[Encrypted]` with an explicit `#[Column(type: 'varchar')]`: respect the declared type or throw? (The plan says "text regardless of PHP type", which says nothing about declared types.)
- Should `Container::has()` also consider `instances` and preferences? That is a core change and out of scope here.
