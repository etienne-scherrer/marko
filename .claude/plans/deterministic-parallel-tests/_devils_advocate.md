# Devil's Advocate Review: deterministic-parallel-tests

## Critical (Must fix before building)
None.

## Important (Should fix before building)

1. **Task 001: `toContain()` takes no message argument.** Pest's `toContain(mixed ...$needles)` treats a second argument as another needle. So `->toContain('2 passed', $result['output'])` would require the output to contain itself, which passes trivially and adds no failure message. Only `toBe(..., $message)`, `toBeTrue($message)`, and `toBeFalse($message)` accept a message. Fix: write the output checks as `expect(str_contains($output, '...'))->toBeTrue($output)` (or `toBeFalse` for the negated case), and use `toBe(0, $output)` for the exit code.
2. **Task 001: global helper names collide across the whole suite.** `composer test` runs every package's tests in one paratest run, so the global functions in Pest test files share one namespace per worker. The names `removeDir`, `removeDirectory`, `cleanupDir`, `cleanupDirectory`, `removeDirRecursive` and `cleanupTempDir` are already declared elsewhere. Fix: give any new recursive-delete helper a unique, file-specific name (e.g. `removeFixturePestCacheDirectory`), or use a local closure.
3. **Task 001: how the child environment is built.** Build it from `getenv()` with no arguments. Do not use `$_ENV`, which is empty when `variables_order` lacks `E`. Besides `PARATEST`, `TEST_TOKEN` and `UNIQUE_TEST_TOKEN`, also drop any `PEST_PARALLEL*` keys as a precaution. This matters because Pest's `Expectation` switches behaviour on `getenv('PARATEST')`.
4. **Task 003: the shutdown test must poll every condition it asserts.** The `delay(0.05)` covers both the subscription counts and `count(EventLoop::getIdentifiers()) < $before`. Polling only the subscription counts would leave the identifier-count race in place. Fix: put all three checks in the poll condition, with `$before` captured before `stop()`. Poll's own `Amp\delay()` timer has fired and been removed by the time the condition runs again, so it does not inflate the count.
5. **Task 003: the disconnect test.** Keep the `hasChannel('shows.42')` false assertion and the `$next->status === 200` assertion, with `$next` opened after the poll. The "subscription count is a proxy for the freed slot" claim checks out: `StreamRequestHandler::reserve()` registers its `onClose` before `ChannelHub::join()` does, and `SseConnection::close()` runs every callback synchronously. So once the subscription count is 0, the slot is free.
6. **Task 003: the log-interval test drops its client.** `SseTestClient::get(...)->waitFor(...)` discards the client, so its socket can be destructed and closed before the 1s tick. Assign it to a variable that lives until the assertion.
7. **Task 002: PollTest must itself be deterministic.** The timeout test should use a short timeout (e.g. 0.2s). Do not assert upper bounds on elapsed time anywhere in PollTest; only assert lower bounds or outcomes, since the point of the change is load tolerance.

## Minor (Nice to address)
- Task 004 points readers to `Poll::until()`, which task 002 creates. It's docs-only, so there is no build blocker, but ordering 004 after 002 avoids citing a path that doesn't exist yet.
- When the parent runs with `--coverage`, an inherited `XDEBUG_MODE=coverage` slows the child down. It is still correct.
- The memoised child result is shared between tests only because paratest distributes by file; worth a one-line comment.

## Questions for the Team
- None blocking.
