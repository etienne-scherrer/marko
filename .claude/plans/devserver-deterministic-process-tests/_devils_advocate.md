# Devil's Advocate Review: devserver-deterministic-process-tests

## Critical (Must fix before building)

1. **`packages/devserver/tests/Pest.php` is never loaded (Task 002).** `phpunit.xml` runs `packages/*/tests` from the root, and Pest only boots the root `tests/Pest.php`. Package helpers are loaded via root `composer.json` `autoload-dev.files` (`packages/*/tests/Helpers.php`, e.g. roadrunner, ratelimiter). A `waitUntil()` in the package's `Pest.php` gives "Call to undefined function" from the root. **Fix:** put the helper in `packages/devserver/tests/Helpers.php`, register it in root `composer.json` `autoload-dev.files` and run `composer dump-autoload`. Because every helper file is loaded globally, give it a package-specific name (`devserverWaitUntil`) and wrap it in a `function_exists` guard.

2. **Task 001 tests need polling, but polling arrives in Task 002.** In "escalates to SIGKILL" and "stops child processes", the child must install `trap '' TERM` (or spawn its child) before `stop()` runs. Today the only thing that makes that happen first is the 150 ms sleep in `start()`, and that is the same race the issue reports. Without a readiness wait, the SIGKILL test can pass by accident (SIGTERM arrives before the trap is installed) or flake. **Fix:** move the helper and its two tests into Task 001. Task 001 tests write a readiness marker file (or child PID file) and poll for it before calling `stop()`.

## Important (Should fix before building)

3. **The `SIGTERM`/`SIGKILL` constants only exist when pcntl is loaded (Task 001).** The plan wants a no-posix/pcntl fallback, but using `SIGKILL` there is an undefined-constant error. **Fix:** use the literals `15`/`9`, as `PidFile::killProcessGroup()` already does. Without posix, escalate with `proc_terminate($process, 9)`.
4. **Group checks need `ValueError`/`@` handling and a `$pid > 0` guard**, as in `PidFile::isProcessGroupRunning()`. Leader liveness must come from `proc_get_status()`, not `posix_kill($pid, 0)`, because a zombie still answers to signal 0.
5. **The `FakeProcessManager` in `DevUpCommandTest` skips the parent constructor**, so a promoted `stopTimeoutSeconds` is never initialized there. `stop()` must return early (no processes registered) before it reads the property.
6. **The SIGKILL tests need a short timeout** (`stopTimeoutSeconds: 0.3`, for example) so they don't add about 3 s per test. The "returns within the timeout" assertion needs generous slack (timeout + 2 s) so it doesn't recreate the flakiness under parallel load.

## Minor (Nice to address)

- `stopAll()` stops services one at a time, so N services that ignore SIGTERM block `Ctrl+C` for N × timeout. Better: send SIGTERM to all of them, then wait on all of them.
- `stop()` closes the pipes before it signals. A child that writes during the grace period gets SIGPIPE. That is acceptable, but it should be documented.
- The plan's Discovery Notes say "two places" but list three lines (69, 204, 215).

## Questions for the Team

- Is a 3 s default right for heavy services (Vite, queue workers)? Should it be configurable via `dev.stop_timeout` instead of only through the constructor/DI?
