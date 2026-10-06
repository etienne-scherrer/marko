# Plan: HTTP Test Client

## Created
2026-10-05

## Status
completed

## Objective
Give `marko/testing` an in-process HTTP test client that boots a Marko application once and sends many requests through the real `Router::handle()` (global and route middleware, controllers), with a cookie jar, `actingAs()`, and a `TestResponse` with assertions.

## Related Issues
Closes #180

## Discovery Notes
- `Router::handle(Request): Response` runs global + route middleware; `MiddlewarePipeline` already renders `HttpExceptionInterface` via `ExceptionRenderer` (#169), so the client sees production-shaped 4xx/5xx responses for HTTP exceptions with no extra work. Non-HTTP throwables propagate.
- `Request`'s constructor takes server/query/post/body/cookies/files (`UploadedFile` from #174), and decodes JSON bodies itself.
- `WorkerRequestHandler` (roadrunner) and `InProcessRequestHarness` (roadrunner tests) both loop `resolvedInstances(ResettableInterface::class)`; extract to `Marko\Core\RequestStateResetter`.
- `AuthManager::guard()` caches guards privately; `GuardInterface` is a container singleton built from `AuthManager::guard()`. `actingAs()` needs `AuthManager::useGuard()` plus overriding the `GuardInterface` instance when the default guard is targeted.
- `packages/testing/tests/fixtures/` (lowercase) already exists; a `tests/Fixtures/` sibling would collide on case-insensitive filesystems, so the fixture app lives at `packages/testing/tests/fixtures/http-app`.
- The root `.gitignore` re-includes only `tests/Fixtures/**/vendor/` (capital F), so the lowercase fixture path needs its own negation, and FixtureTrackingTest must cover it (task 004).
- `Marko\Routing\Http\Cookie` exposes no value/expiry accessors; task 003 adds them for the jar and assertCookie.
- Routing has no HEAD/OPTIONS attributes, and unmatched routes 404 before global middleware runs; the fixture defines its own `Route` subclasses.
- Production renders non-HTTP throwables through the global `ErrorHandlerInterface` (echo + status code, no `Response`), so `withExceptionHandling()` would be pseudo-functionality; it is not added.

## Scope

### In Scope
- `Marko\Core\RequestStateResetter`, used by `WorkerRequestHandler`
- `AuthManager::useGuard()`
- `Marko\Testing\Http\TestClient` (boot/forApplication, verbs, `*Json`, headers, server vars, query, files, cookie jar, actingAs, reset between requests)
- `Marko\Testing\Http\TestResponse` with assertions and accessors
- Pest expectations `toHaveStatus`, `toHaveJsonPath`
- Fixture app, docs page section, README

### Out of Scope
- `withExceptionHandling()` (see Discovery Notes)
- Database refresh (#181)

## Success Criteria
- [x] Every exit criterion of #180 met
- [x] All tests passing
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | RequestStateResetter in core, used by WorkerRequestHandler | - | completed |
| 002 | AuthManager::useGuard() | - | completed |
| 003 | TestResponse and its assertions; Cookie value/expires accessors; marko/routing require | - | completed |
| 004 | Fixture app (+ .gitignore negation and FixtureTrackingTest for lowercase fixtures) | - | completed |
| 005 | TestClient request building and boot-once lifecycle | 001, 003, 004 | completed |
| 006 | TestClient cookie jar | 005 | completed |
| 007 | TestClient actingAs | 002, 005 | completed |
| 008 | Pest expectations toHaveStatus / toHaveJsonPath | 003 | completed |
| 009 | Docs page and README | 005, 006, 007, 008 | completed |

## Architecture Notes
- The client is a plain object (no trait, no base class). Fluent `with*()` methods mutate and return `$this` (the client is a stateful browser, not a value object) — documented.
- One Application per TestClient instance, booted lazily and never cached statically, so container overrides from actingAs cannot leak between tests.
- `WorkerRequestHandler`'s constructor is unchanged; it builds `RequestStateResetter` from its container.
- Assertion failures throw `AssertionFailedException` with the status and a body excerpt in the message.

## Risks & Mitigations
- Native PHP session state across requests in one process: rely on `Session::reset()` via `RequestStateResetter` before every request, the same as the RoadRunner worker.
