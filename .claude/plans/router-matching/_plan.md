# Plan: Router Matching (precedence, 405, HEAD, OPTIONS)

## Created
2026-10-05

## Status
completed

## Objective
Make the router's matching rules correct and HTTP-compliant: deterministic route precedence independent of registration order, 405 with `Allow`, HEAD and OPTIONS handling, and global middleware (including CORS) on every request, matched or not.

## Related Issues
Closes #171

## Discovery Notes
- `RouteMatcher::match()` iterates `RouteCollection::byMethod()` in registration order; first regex hit wins. Memo caches every `method:path` including misses, unbounded.
- `Router::handle()` returns a bare `new Response('Not Found', 404)` before the pipeline runs, so global middleware (CORS, session, security headers) never sees unmatched requests.
- #169 (merged) added `HttpException::notFound()` / `methodNotAllowed()` and pipeline-level rendering through `ExceptionRenderer` in `MiddlewarePipeline`; the terminal handler can throw and every outer middleware decorates the rendered response.
- `Response` is cloned for decoration (`with*()`), preserving subclasses; `StreamingResponse` (marko/sse) overrides `send()` and streams regardless of body.
- Global middleware that reads route context (`AdminAuthMiddleware`, `AuthorizationMiddleware`, `RateLimitMiddleware`, `LayoutMiddleware`, `CacheabilityChecker`) already handles null controller / unmatched routes.
- `RouteMatcherInterface` has 9 test doubles (layout, page-cache, roadrunner) that must implement any new interface method.
- `marko/cors` is not registered globally; it short-circuits every OPTIONS-with-Origin as a preflight and never emits `Access-Control-Expose-Headers`. `marko/security` ships a duplicate `CorsMiddleware` with its own `security.cors.*` config.
- Global middleware order follows module load order (`GlobalMiddlewareResolver`); `sequence.before` lets cors run outermost.

## Scope

### In Scope
- Precedence: static paths first (O(1) hash lookup), then dynamic by static-segment count desc, static-prefix length desc, registration order. Sorted once per method and cached.
- 404 / 405 via `HttpException` thrown from the terminal handler of a pipeline built from global middleware.
- HEAD falls back to GET; HEAD responses never carry a body (`Response::withoutBody()` preserving subclass; `StreamingResponse` does not stream).
- `#[Head]` and `#[Options]` attributes; automatic OPTIONS 204 with `Allow`.
- `RouteMatcherInterface::allowedMethods()`.
- Bounded memo (hits only, capped at 1,000 entries).
- `route:list` in effective match order.
- CORS: global registration (outermost), `paths` config, `Access-Control-Expose-Headers`, preflight detection via `Access-Control-Request-Method`.
- Remove `Marko\Security\Middleware\CorsMiddleware` and the `security.cors.*` config/getters.
- Docs: routing.md, cors.md, security.md, roadrunner-state-leaks.md, architecture.md route attribute list.

### Out of Scope
- Named routes, prefixes, catch-all / constrained params, `#[WithoutMiddleware]` (#172) — ordering is designed so catch-alls can sort last.
- Route cache (#173).
- Route model binding.

## Success Criteria
- [x] `/shows/live` beats `/shows/{id}` in either registration order
- [x] `/a/{x}/c` beats `/a/{x}/{y}` in either order
- [x] POST to GET-only path → 405 with `Allow: GET, HEAD, OPTIONS`
- [x] Unknown path → 404, JSON when `Accept: application/json`
- [x] HEAD on GET route → same status/headers, empty body; explicit `#[Head]`/`#[Options]` wins
- [x] Automatic OPTIONS → 204 with `Allow`
- [x] Cross-origin preflight to POST-only route with marko/cors → 204 with CORS headers
- [x] Global middleware header present on 404 and 405
- [x] Memo bounded after N unique misses
- [x] `route:list` effective order
- [x] #171 todos in tests/Integration/App/KnownGapsTest.php flipped to real integration tests tagged `->issue(171)`
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Route specificity and precedence ordering in RouteCollection | - | completed |
| 002 | RouteMatcher: static lookup, HEAD fallback, allowedMethods, bounded memo | 001 | completed |
| 003 | `#[Head]` and `#[Options]` attributes | - | completed |
| 004 | `Response::withoutBody()` and non-streaming HEAD for StreamingResponse | - | completed |
| 005 | Router: pipeline for unmatched requests, 404/405, automatic OPTIONS, HEAD body stripping | 001, 002, 003, 004 | completed |
| 006 | `route:list` in effective match order | 001 | completed |
| 007 | CORS: global registration, paths, expose headers, preflight detection | 005 | completed |
| 008 | Remove duplicate security CorsMiddleware | - | completed |
| 009 | Documentation | 001-008 | completed |
| 010 | Flip #171 integration todos (CORS preflight, 405) into real tests | 005, 007 | completed |

## Architecture Notes
- Precedence lives in `RouteCollection` (sorted lazily per method, invalidated on `add()`), so the matcher, `route:list` and #172/#173 share one definition of "match order".
- Specificity data is derived in `RouteDefinition`'s constructor (`isStatic`, `staticSegmentCount`, `staticPrefixLength`), keeping it serializable for #173.
- HEAD fallback lives in `RouteMatcher` so middleware that calls the matcher (layout, page-cache) sees the same route the router dispatches.
- The Router strips the body from every HEAD response (including rendered 404/405 pages), since HTTP forbids a HEAD body.
- Shared contracts: `RouteMatcherInterface::allowedMethods(string $path): array` (`[]` = 404; canonical order GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS, then alphabetical); `Response::withoutBody(): static` + `Response::isBodyOmitted(): bool`; `cors.paths` default `['*']`.
- CORS runs outermost via `sequence.before` on every module that declares `globalMiddleware` (page-cache, session-file, session-database, authentication, authorization, layout).
- `#[Head]`/`#[Options]` must also be added to the codeindexer `AttributeParser` route map.

## Risks & Mitigations
- Interface change on `RouteMatcherInterface` breaks test doubles: update all 9 in the same PR.
- CSRF or other global middleware may now run on 404s: documented; existing middleware audited for null controller.
- CORS preflight semantics change (only OPTIONS + `Access-Control-Request-Method` short-circuits): matches the Fetch spec; tests updated.
