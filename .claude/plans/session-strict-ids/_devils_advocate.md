# Devil's Advocate Review: session-strict-ids

## Critical (Must fix before building)
None. The plan's core claims hold: `session_set_save_handler()` wires `validateId`/`updateTimestamp` from the interface, and `SessionMiddleware`'s `$resumed` check (`getId() === $inboundId`) already returns false when PHP swaps in a fresh id. Note: `packages/session/src/Contracts/SessionHandlerInterface.php` already extends `SessionUpdateTimestampHandlerInterface` in this worktree. That means the three test doubles and both shipped handlers currently fail to satisfy the contract. Task 001 has to finish this before anything else runs green.

## Important (Should fix before building)
1. **Task 004 contradicts an existing test.** `SessionMiddlewareTest` line 417 (`treats an invalid inbound session cookie as no cookie and discards an untouched session`) asserts `cookies()->toBeEmpty()`. The new requirement `sends an expired cookie for a malformed session cookie` inverts that, so the old test has to be updated, not left to fail.
2. **Task 004 fake cannot model "store does not know the id".** `createFakeSession()` keeps whatever `setId()` seeded on `start()`. It needs an option (for example `rejectOnStart: true`) that replaces the seeded id with a generated one on `start()`, which is what PHP strict mode does.
3. **Task 004 regression tests already exist.** `emits no cookie at all when a session the client never had is destroyed` (line 441) and `expires the session cookie relative to the clock when the session is destroyed` (line 280) already cover create+destroy without a cookie and destroy with a cookie. Reuse or rename them; don't duplicate them.
4. **Tasks 002/003: `updateTimestamp()` must return `true` when the record is missing**, for example when gc removed it mid-request. If it returns `false`, PHP emits an E_WARNING ("Failed to write session data ... updateTimestamp"), and Marko's error handling may turn that into an exception. Add a test for this.
5. **Task 001: `session.lazy_write` is not pinned.** The `updateTimestamp()` path only runs when `lazy_write=1`. `Session::configure()` sets every other relevant ini value, so a php.ini with `lazy_write=0` would silently bring back a rewrite on every request. Set it explicitly and test it.
6. **Task 002 has two time sources.** `write()` stamps mtime with real filesystem time, while `validateId()`/`updateTimestamp()` use the injected clock. With a `FakeClock` the lifetime check is wrong. `write()` should also `touch($path, $now)`. Separately, PHP's stat cache persists across requests in a RoadRunner worker, so `validateId()` must `clearstatcache(true, $path)` before `file_exists`/`filemtime`.
7. **Task 005: "without rewriting" cannot be observed from the payload alone.** An unchanged session's write produces identical bytes. The integration tests need a recording decorator around the real handler (or a spy connection for the database case) that asserts `updateTimestamp` was called and `write` was not.

## Minor (Nice to address)
- `FileSessionHandler` tests use stream-wrapper paths (`partial-write://`). `touch()` on those needs `stream_metadata`, which is fine as long as those tests never call `updateTimestamp`.
- The existing RoadRunner tests (`StateLeakSpikeTest`, `InProcessRequestHarnessTest`, `WorkerRequestHandlerResetTest`) send random unknown cookies on every request. They will still pass, because the bodies differ by fresh id, but they now exercise the reject path instead of adoption. Consider renaming them for clarity.
- Strict mode adds a `validateId` call on every resumed request, and in `session_regenerate_id` as a collision check. For the database driver that is one extra `SELECT` per request.

## Questions for the Team
- When the database driver sees an expired-but-present row, should `validateId()` also delete it, or leave cleanup to gc?
