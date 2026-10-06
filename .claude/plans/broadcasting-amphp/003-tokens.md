# Task 003: Token signing and subscriber token

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
HMAC-SHA256 subscriber tokens: `AmphpSignature` signs/verifies, `AmphpSubscriberToken` issues them for private channels the `ChannelRegistry` authorizes.

## Requirements (Test Descriptions)
- [x] `it verifies a token it signed`
- [x] `it rejects a forged token`
- [x] `it rejects an expired token`
- [x] `it includes only authorized private channels`
- [x] `it throws when the app key is empty`
- [x] `it builds a stream url with channels and token`
- [x] `it rejects a malformed token without throwing`
- [x] `it omits the token parameter when no private channel is requested`

## Implementation Notes
- Claims and URL format are fixed in `_plan.md` Shared contracts; task 010 verifies tokens against exactly that shape (`c` holds bare names, no `private-`).
- Signatures: `AmphpSubscriberToken::for(list<string|Channel> $channels, ?AuthenticatableInterface $user): string`, `streamUrl(list<string|Channel> $channels, ?string $token = null): string`. Follow `MercureSubscriberToken`.
- Signing/verifying lives in `AmphpSignature` so the server (which has no user/ChannelRegistry context) can verify without the registry.

## Acceptance Criteria
- All requirements have passing tests
