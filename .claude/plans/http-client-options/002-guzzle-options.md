# Task 002: GuzzleHttpClient option forwarding, validation, http_errors, escape hatch

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Rewrite `GuzzleHttpClient::buildOptions()` to validate via `RequestOptions::validate($options, ['guzzle'])`, forward every portable option to Guzzle, map `auth` and `allow_redirects`, honour per-request `http_errors`, and merge the non-portable `guzzle` array verbatim.

## Context
- Related files: packages/http-guzzle/src/GuzzleHttpClient.php, packages/http-guzzle/tests/Unit/GuzzleHttpClientTest.php
- Patterns to follow: existing `createTestableClient()` helper with MockHandler + Middleware::history

## Requirements (Test Descriptions)
- [x] `it throws InvalidRequestOptionException for an unknown option key before sending`
- [x] `it forwards form_params as a url-encoded body`
- [x] `it forwards multipart as a multipart body`
- [x] `it forwards basic auth credentials`
- [x] `it forwards a bearer token as an Authorization header`
- [x] `it forwards connect_timeout, verify and proxy to guzzle`
- [x] `it forwards allow_redirects as a bool or a maximum redirect count`
- [x] `it merges the guzzle escape-hatch options verbatim`
- [x] `it throws when two body options are given`
- [x] `it returns 4xx and 5xx responses when http_errors is false`
- [x] `it throws HttpException with the response attached by default for 4xx responses`

## Acceptance Criteria
- All requirements have passing tests
- No silent dropping of option keys

## Implementation Notes
(Left blank - filled in by programmer during implementation)
