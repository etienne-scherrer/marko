# Task 004: FakeHttpClient

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Implement `Marko\Testing\Fake\FakeHttpClient` implementing `HttpClientInterface`, applying the same option validation and `http_errors` semantics as the real driver.

## Context
- Related files: packages/testing/src/Fake/FakeHttpClient.php (new), packages/testing/tests/Unit/Fake/FakeHttpClientTest.php (new)
- Patterns to follow: FakeMailer (public private(set) recorded arrays, assertXxx throwing AssertionFailedException)

## Requirements (Test Descriptions)
- [x] `it implements HttpClientInterface`
- [x] `it returns a stubbed response for an exact url`
- [x] `it returns a stubbed response for a wildcard url pattern`
- [x] `it returns queued responses sequentially for any url`
- [x] `it throws AssertionFailedException for an unmatched request by default`
- [x] `it returns an empty 200 response for unmatched requests when stray prevention is disabled`
- [x] `it throws a stubbed ConnectionException`
- [x] `it throws HttpException with the response attached for a stubbed 4xx unless http_errors is false`
- [x] `it validates options with the shared RequestOptions rules`
- [x] `it records requests and passes assertSent / assertSentCount / assertNotSent / assertNothingSent`
- [x] `it fails assertSent / assertSentCount / assertNotSent / assertNothingSent with AssertionFailedException`

## Acceptance Criteria
- All requirements have passing tests
- No Guzzle types used

## Implementation Notes
(Left blank - filled in by programmer during implementation)
