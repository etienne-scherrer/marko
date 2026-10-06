# Task 008: Core ApplicationBooted event

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a core event dispatched by `Application::boot()` after every module boot callback has run, on both live and cached boots. Packages can then validate state that other modules set up in their `boot` callbacks, such as guard drivers registered through `GuardDriverRegistry::extend`. Core has no post-boot hook today: `ModuleManifest` has only `boot`, and boot callbacks run in topological module order.

## Context
- Related files: packages/core/src/Application.php (end of boot(), after the boot callback loop), packages/core/src/Event/Event.php, EventDispatcherInterface
- Keep the event as a plain data class (it can carry nothing, or the container). Observers are discovered and cached like any other observer.

## Requirements (Test Descriptions)
- [x] `it dispatches ApplicationBooted after every module boot callback`
- [x] `it dispatches ApplicationBooted on a cached boot`
- [x] `it lets an observer see state registered by any module boot callback`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- docs/packages/core.md mentions the event

## Implementation Notes
Marko\Core\Event\ApplicationBooted is dispatched at the end of Application::boot().
