# Task 002: Subprocess regression test with fixture Pest project

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Reproduce the real install order: run Pest in a subprocess against a fixture test directory whose `Pest.php` contains no require, using the Composer-installed `marko/testing` (path dependency in `vendor/`) and the generated `vendor/pest-plugins.json`.

## Context
- Related files: packages/testing/tests/Fixtures/pest-project/, packages/testing/tests/Feature/PestPluginRegistrationTest.php
- Patterns to follow: packages/roadrunner/tests/Fixtures/app, tests/IntegrationVerificationTest.php subprocess style

## Requirements (Test Descriptions)
- [x] `it registers toHavePushed in a Pest run whose Pest.php has no require`
- [x] `it fails toHavePushed with an assertion failure when nothing was pushed`

## Acceptance Criteria
- Test fails against the old `autoload.files` registration and passes with the plugin
- Fixture spec files are not collected by the monorepo suite

## Implementation Notes
Fixture specs use the `Spec.php` suffix so the root `phpunit.xml` (`*Test.php`) never collects them.
