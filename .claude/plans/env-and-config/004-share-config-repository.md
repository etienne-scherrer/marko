# Task 004: Share ConfigRepositoryInterface

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Mark `ConfigRepositoryInterface` as a singleton in `packages/config/module.php` so config files are discovered and loaded once per container. The repository is read-only, so sharing it is safe under long-running workers.

## Context
- Related files: packages/config/module.php, packages/config/tests/Unit/ModuleBindingsTest.php
- Keep the closure in `bindings` (the existing ModuleBindingsTest asserts this). Add list-style `'singletons' => [ConfigRepositoryInterface::class]`, which `BindingRegistry::registerModule()` handles via `container->singleton()`. Do NOT move the closure into a keyed `singletons` map.
- The "resolved twice" and "loads once" tests need a real `Container` plus `BindingRegistry` and a `ModuleManifest` built from module.php, not just an inspection of the array.

## Requirements (Test Descriptions)
- [x] `it marks ConfigRepositoryInterface as a singleton`
- [x] `it returns the same repository instance when resolved twice`
- [x] `it loads config files only once across resolves`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
