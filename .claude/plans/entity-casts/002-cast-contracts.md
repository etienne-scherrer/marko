# Task 002: Cast Contracts, Resolver and Built-in Casts

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Introduce `CastInterface`, `EquatableCastInterface` and `CastResolver`, and re-express json/enum/datetime/scalar handling as built-in casts so the hydrator has one code path.

## Context
- Already present (treat as the contract, do not redefine): Entity/Cast/CastInterface.php, EquatableCastInterface.php, JsonCast.php, EnumCast.php, ScalarCast.php, Config/DatabaseTimezoneConfig.php
- New: Entity/Cast/DateTimeCast.php (UTC formatting placeholder; timezone handling is task 003), Entity/Cast/CastResolver.php
- `CastResolver::__construct(?ContainerInterface $container = null)`, `resolve(string $castClass): CastInterface`, cached per class
- `EntityHydrator::__construct(?EntityMetadataFactory $metadataFactory = null, ?CastResolver $castResolver = null)` — null resolver means internal `new CastResolver()`; the ~26 `new EntityHydrator()` test call sites must keep working
- Precedence for choosing the built-in cast is defined in _plan.md "Interface Contracts"; the `castClass` slot is wired in task 004
- Note: the container always resolves nullable class constructor params (it never passes null), so `ContainerInterface` (registered as an instance) will be injected into CastResolver in apps

## Requirements (Test Descriptions)
- [x] `it resolves cast classes through the container`
- [x] `it instantiates no-argument casts without a container`
- [x] `it throws a clear exception when a cast cannot be built without a container`
- [x] `it throws when a class does not implement CastInterface`
- [x] `it caches resolved cast instances`
- [x] `it converts json, enum, datetime and scalar values through built-in casts`
- [x] `it constructs EntityHydrator with no arguments`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
