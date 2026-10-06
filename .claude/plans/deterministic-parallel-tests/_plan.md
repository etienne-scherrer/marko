# Plan: Deterministic Parallel Tests

## Created
2026-10-06

## Status
completed

## Objective
Make `PestPluginRegistrationTest` (marko/testing) and `AmphpSseServerTest` (marko/broadcasting-amphp) deterministic under `composer test --parallel` on a fully loaded machine, by removing shared state and fixed-duration waits.

## Related Issues
Closes #290

## Discovery Notes
- `PestPluginRegistrationTest` boots two cold child Pest processes, with a stripped `PATH`/`HOME` environment (drops `TMPDIR`, no `memory_limit`), and a fixed shared cache directory under `sys_get_temp_dir()`.
- `AmphpSseServerTest` waits with `delay()` for the log-interval tick (`delay(1.2)`, 200ms margin), for a disconnect to surface on a heartbeat (`delay(2.5)`), and for shutdown cleanup (`delay(0.05)`).
- `SseTestClient` waits cap at 2.0s.
- Disconnect cleanup runs from `SseConnection::onClose` callbacks: `ChannelHub::leave()` cancels the subscription and `StreamRequestHandler::reserve()` frees the slot, in the same close, so polling the subscription count is a sound proxy for the freed slot.
- `tests/Support` is PSR-4 autoload-dev only (`Marko\Broadcasting\Amphp\Tests\Support\`), so the poll helper is a class with a static method rather than a namespaced function (no `files` autoload entry needed).
- Test-only change; no production code touched.

## Scope

### In Scope
- Single child Pest run per file, per-run cache dir removed afterwards, parent env minus paratest variables, explicit `memory_limit=2G`, child output in every failure message, comment on the `only.lock` side effect
- `Poll::until()` helper in `packages/broadcasting-amphp/tests/Support`
- Replace wait-for-condition `delay()` calls in `AmphpSseServerTest`
- `SseTestClient` ceilings raised to 5s
- `.claude/testing.md` contributor rule

### Out of Scope
- `keeps an idle stream open longer than the HTTP driver stream timeout` (must wait past the driver timeout)
- `does not log connection counts when log_interval is 0` (asserts absence over a window; not a wait-for-condition)
- Working around the child `Only` plugin deleting `only.lock`
- Any production code or user-facing docs

## Success Criteria
- [x] `PestPluginRegistrationTest` starts at most one child Pest process per run
- [x] Every assertion there carries the child output
- [x] No `delay()` in `AmphpSseServerTest` used as a wait-for-condition; each site polls with timeout >= 5s
- [x] `SseTestClient` ceilings are 5s
- [x] Repeated parallel stress runs pass
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Run the fixture Pest project once with an isolated cache and environment | - | completed |
| 002 | Add the Poll helper and raise SseTestClient ceilings | - | completed |
| 003 | Replace fixed delays in AmphpSseServerTest with polling | 002 | completed |
| 004 | Document the deterministic test rules in .claude/testing.md | - | completed |

## Architecture Notes
- `Poll::until(callable $condition, string $description, float $timeout = 5.0, float $interval = 0.05): void` polls with `Amp\delay()` (so the event loop keeps running server fibers) and throws `RuntimeException` naming the condition and timeout.
- Memoise the child run in a `static` inside the helper so both tests share one process.
- Pest's `toContain()` has no message parameter; attach child output via `toBeTrue($output)` / `toBeFalse($output)` / `toBe(0, $output)` (task 001).
- Global helper functions in test files share one namespace across the whole suite run; new helpers need unique names (task 001).
- The shutdown test's poll condition must also cover the event-loop identifier count, not just subscriptions (task 003).

## Risks & Mitigations
- Memoised result shared across tests in the same worker: tests are read-only on the result, so sharing is safe.
- Temp-dir cleanup failing silently: remove recursively in a `finally`; leftover dirs only waste temp space and never affect other runs because names are unique.
