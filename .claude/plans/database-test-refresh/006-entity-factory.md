# Task 006: EntityFactory

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`abstract class Marko\Database\Testing\EntityFactory`: definition() returns a constructed entity; make(callable ...$states), create(...) persists through the repository resolved from the container via `protected const REPOSITORY`; makeMany/createMany(int $count, ...); sequence(callable ...$states) cycles states.

## Requirements (Test Descriptions)
- [ ] `it makes an entity from the definition`
- [ ] `it applies state closures in order after the definition`
- [ ] `it makes a list of entities with makeMany`
- [ ] `it cycles sequence states across the entities it builds`
- [ ] `it creates an entity through the repository so lifecycle events fire`
- [ ] `it throws when create is used without a container`
- [ ] `it throws when REPOSITORY is not a repository`
- [ ] `it throws a clear exception when REPOSITORY is not defined`
- [ ] `it calls definition once per entity so makeMany returns distinct instances`
- [ ] `it leaves the original factory unchanged when sequence is applied`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Constructor: `__construct(?ContainerInterface $container = null)`; `make*` works without a container, `create*` requires one.
- `REPOSITORY` cannot be enforced by an abstract class; check `defined(static::class . '::REPOSITORY')` and throw a clear exception, then require `is_subclass_of(..., RepositoryInterface::class)`.
- State closures: `callable(TEntity): void` (they mutate the entity), applied in order after `definition()`.
- `sequence()` returns a new factory (clone); it does not mutate the receiver.
- `create()` persists with `RepositoryInterface::save()` (fires `EntityCreating`/`EntityCreated`).
- Annotate `@template TEntity of Entity` so PHPStan types `make()`/`create()` returns.
