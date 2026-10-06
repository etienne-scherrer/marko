# Task 002: Presence authorizer contract, discovery and ChannelRegistry::authorizePresence

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `PresenceChannelAuthorizerInterface`, let discovery register it, and add `ChannelRegistry::authorizePresence()`. Mismatched authorizer kinds fail loudly in both directions.

## Context
- Related files: packages/broadcasting/src/ChannelRegistry.php, ChannelDefinition.php, Attributes/BroadcastChannel.php, Discovery/BroadcastChannelDiscovery.php, Exceptions/ChannelAuthorizationException.php, tests/Unit/ChannelRegistryTest.php, tests/Fixtures/
- Patterns to follow: existing authorize() and fixtures
- Exact shapes: see "Interface Contracts" in `_plan.md`
- **Fixtures:** do NOT add presence authorizers to `ValidModule`. The existing tests `discovers BroadcastChannel classes...` and `lists the registered channel patterns` assert its exact contents. Create `tests/Fixtures/PresenceModule/src/RoomPresenceAuthorizer.php` (namespace `Marko\Broadcasting\Tests\Fixtures\PresenceModule`, pattern `rooms.{roomId}`). It returns a `PresenceMember` for a non-null user and null for guests. Parameterize the `channelRegistry()` test helper with a fixture name that defaults to `ValidModule`. Check that the fixture namespace is autoloadable the same way `ValidModule` is.
- **Discovery:** accept a class implementing either `ChannelAuthorizerInterface` or `PresenceChannelAuthorizerInterface`. `notAnAuthorizer()` must name both interfaces and keep the substring `must implement ChannelAuthorizerInterface`, which an existing test asserts.
- **ChannelDefinition:** widen the docblock to `class-string<ChannelAuthorizerInterface|PresenceChannelAuthorizerInterface>`. PHPStan must stay at zero errors.
- **ChannelRegistry::authorize():** replace the `/** @var ChannelAuthorizerInterface */` cast with an `instanceof` check. A `PresenceChannelAuthorizerInterface` match throws `privateAuthorizerRequired()`. This also covers Mercure, amphp and Pusher private paths, which all call `authorize()`.
- **ChannelRegistry::authorizePresence():** a `ChannelAuthorizerInterface` match throws `presenceAuthorizerRequired()`. No match throws `unknownPresenceChannel()`, whose message says "presence channel" and suggests `PresenceChannelAuthorizerInterface` (the existing `unknownChannel()` says "private channel").
- Update the `#[BroadcastChannel]` attribute docblock to mention both authorizer kinds
- A private and a presence authorizer on the same pattern are rejected by the existing `duplicatePattern` check. Keep that behaviour.

## Requirements (Test Descriptions)
- [x] `it discovers presence channel authorizers`
- [x] `it returns the member when the presence authorizer allows the user`
- [x] `it returns null when the presence authorizer denies the user`
- [x] `it passes named pattern parameters to the presence authorizer`
- [x] `it throws a clear exception when a private-only authorizer matches a presence channel`
- [x] `it throws a clear exception when a presence authorizer matches a private channel`
- [x] `it throws ChannelAuthorizationException for an unknown presence channel` (message mentions presence channel)
- [x] Existing ChannelRegistryTest tests still pass unchanged

## Acceptance Criteria
- All requirements have passing tests
- ChannelAuthorizerInterface unchanged
- PHPStan zero errors

## Implementation Notes
Added PresenceChannelAuthorizerInterface, widened discovery and ChannelDefinition, ChannelRegistry::authorizePresence() plus instanceof guard in authorize(), three new ChannelAuthorizationException factories, PresenceModule fixture. Package tests pass; phpcs clean.
