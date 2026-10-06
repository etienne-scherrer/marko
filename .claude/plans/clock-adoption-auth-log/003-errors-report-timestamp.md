# Task 003: errors — report timestamp from the handler's clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`ErrorReport::fromThrowable()` takes the report timestamp as an argument instead of reading the system time. `SimpleErrorHandler` and `AdvancedErrorHandler` inject `ClockInterface` and pass `$clock->now()`.

## Context
- Related files: packages/errors/src/ErrorReport.php, packages/errors-simple/src/SimpleErrorHandler.php, packages/errors-advanced/src/AdvancedErrorHandler.php, packages/errors-advanced/module.php, composer.json of errors-simple and errors-advanced

## Requirements (Test Descriptions)
- [ ] `it uses the given timestamp for the report`
- [ ] `it stamps exception reports with the injected clock` (errors-simple)
- [ ] `it stamps error reports with the injected clock` (errors-simple)
- [ ] `it stamps exception reports with the injected clock` (errors-advanced)
- [ ] `it builds the handler with the bound clock from the module` (errors-advanced)

## Acceptance Criteria
- All requirements have passing tests
- errors-simple and errors-advanced require `marko/clock`
- errors-simple and errors-advanced add `"marko/testing": "self.version"` to `require-dev` (FakeClock). Today they only have pest.
- Every existing call site of `ErrorReport::fromThrowable()`, `new SimpleErrorHandler(` and `new AdvancedErrorHandler(` in tests updated

## Implementation Notes
`marko/errors` itself no longer reads time, so it does not need `marko/clock`.

Exact signatures:
- `ErrorReport::fromThrowable(Throwable $throwable, Severity $severity, DateTimeImmutable $timestamp)`
- `SimpleErrorHandler::__construct(Environment $environment, ClockInterface $clock, ?TextFormatter $textFormatter = null, ?BasicHtmlFormatter $htmlFormatter = null)`. Autowired through errors-simple's class-string binding, so module.php needs no change.
- `AdvancedErrorHandler::__construct(ClockInterface $clock, ?Environment $environment = null, ?FormatterInterface $prettyHtmlFormatter = null)`. The clock goes first because a required parameter cannot follow the optional ones. errors-advanced/module.php passes `clock: $container->get(ClockInterface::class)`.

Existing test call sites that break (about 60 `fromThrowable` calls plus the handler constructions):
- errors/tests/Unit/ErrorReportTest.php
- errors-simple/tests/Unit/{SimpleErrorHandlerTest,HttpAwareErrorHandlingTest}.php, tests/Unit/Formatters/{BasicHtmlFormatterTest,TextFormatterTest}.php, tests/Feature/ErrorHandlingTest.php
- errors-advanced/tests/Unit/{AdvancedErrorHandlerTest,PrettyHtmlFormatterTest,IntegrationTest,UrlLinkificationTest}.php, tests/Integration/ErrorHandlerChainTest.php

Grep for all three patterns across `packages/` before finishing.
