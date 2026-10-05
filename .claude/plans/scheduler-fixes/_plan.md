# Plan: Scheduler Fixes

## Created
2026-10-05

## Status
completed

## Objective
Make `marko/scheduler` work out of the box (tasks registered in a module `boot` callback are seen by `schedule:run`), make failures visible through the exit code, add `withoutOverlapping()` protection backed by a file mutex, and add a foreground `schedule:work` runner.

## Related Issues
Closes #164

## Discovery Notes
- `packages/scheduler/module.php` returned `['bindings' => []]`; the container is transient by default, so the `Schedule` filled in a `boot` callback was not the one injected into `RunScheduleCommand`.
- `BindingRegistry` supports list-style singletons; module `boot` callbacks run at the end of `Application::initialize()`, and commands are resolved lazily when run, so marking `Schedule` a singleton is sufficient.
- `RunScheduleCommand` swallowed all `Throwable`s and returned `0`.
- No lock abstraction exists in the framework; `CacheInterface` has no atomic add. Only `flock()` inside file drivers.
- No PSR-20 clock package exists yet (#182 not landed), so time sources are injectable `Closure`s.
- `ProjectPaths::$base` is the project root; other modules resolve it via a closure binding in `module.php`.
- Container autowiring passes the default value for `?Closure` constructor params, so injectable clocks/sleepers with `null` defaults stay autowirable.
- `marko/devserver` `ProcessManager::runForeground()` is the precedent for `pcntl_signal` + `pcntl_async_signals` guarded by `function_exists`.

## Scope

### In Scope
- `Schedule` singleton in `module.php` + container-level regression test
- `ScheduleRunner` service shared by `schedule:run` and `schedule:work`; `schedule:run` exits `1` when any task fails while still running the rest
- `ScheduledTask::withoutOverlapping(int $expiresAfterMinutes = 1440)` + `SchedulerException`
- `Marko\Scheduler\Mutex\TaskMutexInterface` + `FileTaskMutex` (flock + stored expiry, stale reclaim), bound in `module.php`
- `schedule:work` command (minute-boundary loop, SIGINT/SIGTERM stop, injectable clock and sleeper)
- Docs page and README updates

### Out of Scope
- A framework-wide `marko/lock` package
- Redis/cache-backed mutexes (can be swapped via Preference later)
- Running tasks in background processes / `runInBackground()`
- PSR-20 clock (#182)

## Success Criteria
- [x] A task registered through a module `boot` closure is executed by `schedule:run` (container-level test)
- [x] `schedule:run` exits `1` when a task throws, and still runs the other due tasks
- [x] `withoutOverlapping()`: held mutex skips the task; stale mutex is reclaimed; mutex released when the task throws
- [x] `withoutOverlapping()` without `description()` throws a helpful exception
- [x] `schedule:work` runs due tasks on minute boundaries (no real sleeping in tests)
- [x] Docs page and README updated
- [x] All tests passing
- [x] Code follows project standards (`composer ci` green)

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Schedule singleton + boot-registration regression test | - | completed |
| 002 | ScheduledTask::withoutOverlapping() + SchedulerException | - | completed |
| 003 | TaskMutexInterface + FileTaskMutex + module binding | 001, 002 | completed |
| 004 | ScheduleRunner + schedule:run exit code and overlap handling | 001, 002, 003 | completed |
| 005 | schedule:work command | 004 | completed |
| 006 | Docs page and README | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Scheduler-local mutex interface (no dependency on cache/redis packages); multi-server setups swap the binding via Preference.
- Mutex key: `schedule-` + sha1(expression + description) — description required because closures have no stable identity.
- `FileTaskMutex` holds an `flock(LOCK_EX | LOCK_NB)` on `storage/framework/schedule-{hash}` for the lifetime of the task and writes the expiry timestamp into the file. A crashed holder's flock is released by the kernel; a hung holder past its expiry is reclaimed by unlinking the file and locking a fresh inode. After locking, the handle's inode is compared with the path's inode to avoid the unlink race.
- `schedule:work` runs tasks inline (foreground, sequentially); a run longer than a minute delays to the next minute boundary.

## Risks & Mitigations
- flock semantics differ across filesystems (NFS): documented; multi-server setups should swap in a distributed mutex.
- Signal handlers installed during tests: restored to `SIG_DFL` when the loop exits.
