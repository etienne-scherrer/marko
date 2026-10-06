# Task 002: broadcasting-amphp signature, token, ids and ReplayBuffer via clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `AmphpSignature` (token expiry check), `AmphpSubscriberToken` (token expiry), `AmphpBroadcaster` (time-ordered event ids) and `AmphpSseServer` (which builds the `ReplayBuffer`). Replace `ReplayBuffer`'s `Closure` clock with a required `ClockInterface` outright: it is constructed only by the server and is not documented public API.

## Context
- Related files: packages/broadcasting-amphp/src/{Auth/AmphpSignature.php, Subscriber/AmphpSubscriberToken.php, Driver/AmphpBroadcaster.php, Server/ReplayBuffer.php, Server/AmphpSseServer.php}, tests constructing them
- Patterns to follow: packages/session; FakeClock from marko/testing
- Test constructions that must be updated (missing one fatals at runtime):
  - `tests/Feature/AmphpSseServerTest.php`: the anonymous `AmphpSseServer` subclass's constructor calls `parent::__construct(...)` with 4 args. Add the clock parameter and forward it.
  - `tests/Unit/Server/ChannelHubTest.php`: `new ReplayBuffer(size: 100, ttl: 300)` must now pass a clock.
  - `tests/Unit/Server/ReplayBufferTest.php`: replace the closure helper with FakeClock (`travel('+N seconds')`).
  - `tests/Feature/ServeCommandTest.php` and `tests/Feature/RedisLiveTest.php`. RedisLiveTest is `integration-destructive`, so `composer test` skips it. Fix it anyway and run `composer test:all` or at least lint it with phpstan.
- `generateId`: keep the `%013d` zero-padded integer prefix. Pass `(int) $clock->now()->format('Uv')`, not the raw string.
- `ReplayBuffer` time: `(float) $clock->now()->format('U.u')` keeps sub-second precision for `SseEvent::$receivedAt`.

## Requirements (Test Descriptions)
- [x] `it accepts a token up to its expiry second on the injected clock and rejects it one second later`
- [x] `it signs subscriber tokens that expire token_ttl seconds after the injected clock`
- [x] `it prefixes generated event ids with the injected clock in unix milliseconds`
- [x] `it evicts replay events once the injected clock passes the ttl` (ReplayBuffer tests use FakeClock instead of a closure)
- [x] composer.json requires marko/clock

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
