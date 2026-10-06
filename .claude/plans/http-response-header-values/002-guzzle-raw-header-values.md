# Task 002: GuzzleHttpClient passes raw multi-value headers

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`GuzzleHttpClient` passes Guzzle's raw `getHeaders()` as `headerValues` for both returned responses and responses attached to `HttpException`, alongside the flattened `headers` map.

## Context
- Related files: packages/http-guzzle/src/GuzzleHttpClient.php, packages/http-guzzle/tests/Unit/GuzzleHttpClientTest.php

## Requirements (Test Descriptions)
- [x] `it passes repeated response header values through intact`
- [x] `it passes repeated header values through on the response attached to an HttpException`
- [x] `it joins repeated response header values with a comma` (existing, still passes)

## Acceptance Criteria
- All requirements have passing tests
- `composer phpstan` reports zero errors. Guzzle's `getHeaders()` is typed `string[][]`, not `array<string, list<string>>`, so normalise the values with `array_values()` in a small private helper instead of passing `getHeaders()` straight in.
- The `flattenHeaders()` docblock no longer calls the driver lossy without qualification. It points to `HttpResponse::headerValues()` for intact repeated values.

## Implementation Notes
Implemented with TDD; see the PR for #220.
