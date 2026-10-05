# Task 001: RequestOptions and InvalidRequestOptionException

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Define the portable request option set in `marko/http` as constants on `Marko\Http\RequestOptions`, with a shared `validate()` that every driver and the fake call, and a new `InvalidRequestOptionException` extending `MarkoException`.

## Context
- Related files: packages/http/src/RequestOptions.php (new), packages/http/src/Exceptions/InvalidRequestOptionException.php (new), packages/http/src/Contracts/HttpClientInterface.php (docblocks)
- Patterns to follow: packages/http/src/Exceptions/NoDriverException.php (static factories with message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it exposes every portable option key as a constant`
- [x] `it accepts an empty options array`
- [x] `it accepts every supported option key`
- [x] `it throws InvalidRequestOptionException naming an unknown key and listing the supported keys`
- [x] `it accepts driver-specific extra keys passed to validate`
- [x] `it throws when more than one body option is given`
- [x] `it throws when auth is neither a basic pair nor a bearer token`
- [x] `it throws when http_errors is not a boolean`
- [x] `it reports whether a request should throw on http errors, defaulting to true`

## Acceptance Criteria
- All requirements have passing tests
- Exception extends MarkoException with message, context and suggestion

## Implementation Notes
(Left blank - filled in by programmer during implementation)
