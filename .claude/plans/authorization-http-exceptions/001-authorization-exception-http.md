# Task 001: AuthorizationException as a 403 HTTP exception

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Make `Marko\Authorization\Exceptions\AuthorizationException` implement `HttpExceptionInterface` (403) so a denied `Gate::authorize()` renders as 403 instead of 500. Move policy misconfiguration errors to a new `PolicyException` so they stay loud 500s.

## Context
- Related files: packages/authorization/src/Exceptions/AuthorizationException.php, packages/authorization/src/Gate.php, packages/authorization/src/PolicyRegistry.php
- Patterns to follow: packages/database/src/Exceptions/EntityNotFoundException.php

## Requirements (Test Descriptions)
- [x] `it implements HttpExceptionInterface with a 403 status and no headers`
- [x] `it never exposes the ability or resource name in the response data`
- [x] `it keeps the ability and resource for logging`
- [x] `it throws a 403 AuthorizationException from authorize when denied`
- [x] `it throws PolicyException when a policy is registered twice`
- [x] `it throws PolicyException when the policy method does not exist`
- [x] `it does not implement HttpExceptionInterface on PolicyException`
- [x] `it forwards context and suggestion to MarkoException`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`AuthorizationException` now extends `MarkoException` (context/suggestion inherited). `missingPolicy()` removed: nothing threw it (the Gate denies by default when no policy is registered).

Constructor contract (named args are used by callers and docs):
`__construct(string $message = 'Forbidden', string $ability = '', string $resource = '', string $context = '', string $suggestion = '', ?Throwable $previous = null)`.
Pass context/suggestion/previous to `parent::__construct()`. Do NOT redeclare `$context`/`$suggestion` properties or `getContext()`/`getSuggestion()`: the current class has its own private copies, and they would shadow the parent's. `getStatusCode()` returns 403, `getHeaders()` returns `[]`, and `getResponseData()` returns `['message' => 'Forbidden.']` (fixed; see `EntityNotFoundException`).

`PolicyException extends MarkoException` (not HTTP) in `packages/authorization/src/Exceptions/PolicyException.php`, with factories:
- `duplicatePolicy(string $entityClass, string $policyClass, string $existing)`, thrown by `PolicyRegistry::register()`
- `missingMethod(string $policyClass, string $ability)`, thrown by `Gate::callPolicy()` (reachable from `allows()`, `denies()` and `authorize()`)

Update `@throws` docblocks: `PolicyRegistry::register()` → `PolicyException`; `GateInterface::allows()`/`denies()`/`authorize()`/`policy()` and `Gate` to match (`authorize()` throws `AuthorizationException|PolicyException`).

Existing tests to update: `tests/Unit/Exceptions/AuthorizationExceptionTest.php` (constructor, `missingPolicy` and context tests), `tests/Unit/PolicyRegistryTest.php:151` (duplicate → `PolicyException`; also rename the misnamed test at line 145), and `tests/Unit/GateTest.php` / `GatePolicyIntegrationTest.php` (assert the 403 status on denial).
