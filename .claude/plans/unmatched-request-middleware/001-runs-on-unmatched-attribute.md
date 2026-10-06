# Task 001: RunsOnUnmatched attribute and router filtering

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a `#[RunsOnUnmatched]` class attribute to marko/routing. When no route matches, the Router runs only the global middleware whose declared class carries it.

## Context
- Related files: packages/routing/src/Router.php, packages/routing/src/Attributes/, packages/routing/tests/RouterMatchingTest.php
- Patterns to follow: `#[WithoutMiddleware]` and `Router::middlewareFor()`

## Requirements (Test Descriptions)
- [x] `it runs global middleware marked with RunsOnUnmatched on 404 and 405 responses`
- [x] `it skips global middleware without RunsOnUnmatched for unmatched requests`
- [x] `it still runs every global middleware for matched routes`
- [x] `it answers automatic OPTIONS without running unmarked global middleware`
- [x] `it targets classes only`
- [x] `it skips a global middleware whose declared class does not exist on unmatched requests` (no ReflectionException)
- [x] Rewrite `packages/routing/tests/WithoutMiddlewareTest.php` "still runs every global middleware for unmatched requests" (line ~132) — it contradicts the new behaviour; assert only opted-in middleware runs
- [x] Update the `Router::handle()` docblock ("Unmatched requests still run every global middleware")

## Implementation Constraints
- Reflect on the attribute ONLY in the unmatched path (never in the constructor or for matched routes): `RouterTest` passes non-existent class strings such as `'App\\Middleware\\GlobalMiddleware'` with a mocked container, and constructor-time reflection would break them.
- Guard with `class_exists()` before `new ReflectionClass()`.
- Read the attribute from the declared class name (not the container-resolved instance) per `_plan.md` Architecture Notes.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented as `Marko\Routing\Attributes\RunsOnUnmatched` plus `Router::unmatchedMiddleware()`.
