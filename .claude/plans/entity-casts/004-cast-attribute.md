# Task 004: #[Cast] Attribute and Cast-aware Dirty Checking

**Status**: pending
**Depends on**: 002
**Retry count**: 0

## Description
Add `#[Cast(SomeCast::class)]`, read it into `PropertyMetadata::$castClass`, and use it in hydrate/extract/update and dirty checking.

## Context
- Files: Attributes/Cast.php (new), Entity/PropertyMetadata.php (trailing `?string $castClass = null`), Entity/EntityMetadataFactory.php, Entity/EntityHydrator.php, Repository/Repository.php (criteria binding)
- Dirty snapshot design is fixed in _plan.md "Interface Contracts": `getOriginalValues()` stays PHP values (public API, existing tests assert it); add a second WeakMap of database representations for cast properties only, populated by `hydrate()` (parent and extender branches) and `registerOriginalValues()`. Do not snapshot DB representation for non-cast properties (avoids doubling hydrate cost).
- `EntityMetadataFactory::parse()` currently throws `jsonColumnTypeMismatch` when `type: 'json'` is used on a non-array property — skip that check when a `#[Cast]` is present so JSON-backed value objects are possible.

## Requirements (Test Descriptions)
- [ ] `it reads the cast class into property metadata`
- [ ] `it throws when the cast class does not implement CastInterface`
- [ ] `it round-trips a custom cast through hydrate, insert, update and hydrate`
- [ ] `it does not mark an unchanged value-object property dirty`
- [ ] `it marks a value-object property dirty when its database representation changes`
- [ ] `it uses the cast equals hook when the cast implements EquatableCastInterface`
- [ ] `it detects an in-place mutation of a mutable value object as dirty`
- [ ] `it keeps getOriginalValues returning PHP values for cast properties`
- [ ] `it applies casts to extender companion properties`
- [ ] `it allows a json column type on a cast property with a non-array PHP type`
- [ ] `it converts findBy, findOneBy and existsBy criteria for cast properties through the pipeline`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
