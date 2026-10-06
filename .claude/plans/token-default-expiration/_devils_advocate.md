# Devil's Advocate Review: token-default-expiration

## Critical (Must fix before building)
None. The constructor change, autowiring (`TokenConfig` takes only `ConfigRepositoryInterface`; `ClockInterface` is bound by `marko/clock`, already in `require`) and the guard's existing expiry check all hold up against the source.

## Important (Should fix before building)

1. **Task 003: integration test not listed as a caller.** `tests/Integration/App/AuthTest.php:65` resolves `TokenManager` from the real app container (group `integration-services`, which `composer test` runs). It must still pass with the real `ConfigRepository` and the system clock. The `valid` token now gets a 365-day expiry, and the `expired` token keeps its explicit `-1 hour`. Added to the task context so the worker runs it.

2. **Task 003: the wiring test container has no `authentication-token.token_expiration_days` key.** `bootTokenContainer()` builds a `FakeConfigRepository` without it, so resolving `TokenManager` from that container throws `ConfigNotFoundException`. `issueToken()` also does `new TokenManager($tokenRepository)` with the old signature. `it reaches the #[Can] gate check with a valid token` calls `issueToken()` without `expiresAt`, so that token now gets a default expiry, and the result depends on which clock `issueToken()` uses. Fix (now in task 003): add the shipped value to `bootTokenContainer()`'s config, and have `issueToken()` either resolve `TokenManager` from a container booted with the same `$now`, or build it with a `FakeClock` at that `$now`. Doing so also covers the "resolves from the container" requirement.

3. **Task 003: the `createdAt` format was not specified.** The entity column is `?string`, and `expiresAt` uses `Y-m-d H:i:s`. Set `createdAt` with the same format so the database stores comparable values. This is added to the task, and #276's timezone handling is still out of scope.

4. **Task 003: use `get()` in TokenConfig, not `getInt()`.** This already holds for the in-progress `TokenConfig`. Noted for task 003 because `FakeConfigRepository::getInt()` does `(int) null === 0`, so a test that stubs `TokenConfig` through `getInt` would hide the `null` opt-out. No change needed beyond keeping `TokenConfig` as the only reader.

## Minor (Nice to address)
- Task 002: `it has no remaining references to the removed exceptions in the package source` is a grep-style test. It's of little value long-term, so consider deleting the class tests and relying on PHPStan and `class_exists` checks instead.
- Task 004: the "test descriptions" are documentation checks, not Pest tests. Workers should treat them as a review checklist.
- Task 003: decide whether `expirationDays()` is read even when an explicit `expiresAt` is given. Reading it only when needed avoids throwing on bad config the call does not use. Reading it always is louder. Either way is fine, but pick one and test it.
- `TokenConfig` and `TokenConfigTest` already exist in the worktree (task 001 appears to be in progress). They match the plan.

## Questions for the Team
- **`TokenException` after removal:** it becomes orphaned (no subclasses, never thrown; `StatelessGuardException` extends `AuthException`). Should it be kept as the package's base exception or deleted under "no pseudo-functionality"? Task 002 leaves this to the worker. Whatever is chosen, docs line 325 must match (task 004 runs after 002).
- **Numeric strings from env:** `TokenConfig` rejects `"30"` (for example from an `env()` value). Real `ConfigRepository::getInt()` accepts numeric strings. Is strict `int` the intended behaviour?
