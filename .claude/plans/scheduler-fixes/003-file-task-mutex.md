# Task 003: TaskMutexInterface + FileTaskMutex

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Add `Marko\Scheduler\Mutex\TaskMutexInterface` (`acquire`, `release`, `exists`) and a `FileTaskMutex` default implementation using `flock(LOCK_EX | LOCK_NB)` with a stored expiry timestamp so a stale lock can be reclaimed. Bind it in `module.php` under `{base}/storage/framework`.

## Context
- Related files: `packages/scheduler/module.php`, `packages/core/src/Path/ProjectPaths.php`
- Patterns to follow: closure bindings using `ProjectPaths` in `packages/devserver/module.php`

## Requirements (Test Descriptions)
- [x] `it acquires the mutex when no other holder exists`
- [x] `it refuses to acquire a mutex held by another holder`
- [x] `it reclaims a mutex whose holder is past its expiry`
- [x] `it allows acquiring again after release`
- [x] `it reports whether the mutex exists`
- [x] `it creates the mutex directory when missing`
- [x] `it binds TaskMutexInterface to FileTaskMutex under storage/framework in module.php`

## Acceptance Criteria
- All requirements have passing tests
- Time source injectable (no real waiting in tests)

## Implementation Notes
(Left blank - filled in by programmer during implementation)
