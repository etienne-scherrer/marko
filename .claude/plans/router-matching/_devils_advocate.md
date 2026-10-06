# Devil's Advocate Review: router-matching

Note: the ticket body (`gh api repos/marko-php/marko/issues/171`) could not be fetched from this review environment (no shell). Exit criteria were taken from `_plan.md` and from the #171 todos in `tests/Integration/App/KnownGapsTest.php`. Tasks 001 and 002 already have uncommitted implementations in the worktree (`RouteCollection::staticRoute()/methods()`, `RouteMatcher::allowedMethods()`, clear-all memo at 1,000).

## Critical (Must fix before building)

1. **No task flips the #171 integration todos (all tasks / exit criteria).** `tests/Integration/App/KnownGapsTest.php:154-159` holds two #171 todos, and the file header says the owning ticket turns its todo into a real test as part of its exit criteria. No task covers that. Doing it means adding `cors` to `INTEGRATION_MODULES` in `tests/Integration/App/Helpers.php`, adding a fixture `config/cors.php`, and tagging the new tests `->issue(171)` so `HarnessTest` ("lists a todo naming each owning ticket") still passes. The 405 todo's note says `Allow: GET, HEAD`, but the plan returns `GET, HEAD, OPTIONS`. **Fix:** added task 010 (depends on 005 and 007), using the plan's `Allow` value.

## Important (Should fix before building)

2. **Task 003: codeindexer does not know the new attributes.** `packages/codeindexer/src/Attributes/AttributeParser.php:207-213` hardcodes the Get/Post/Put/Patch/Delete map. `#[Head]` and `#[Options]` routes would not appear in the code index (LSP/MCP). **Fix:** added this to task 003.
3. **Task 005: existing tests assert the old bare 404 body.** `packages/routing/tests/RouterTest.php:81` and `packages/routing/tests/Integration/DemoRoutingTest.php:112` expect `body() === 'Not Found'`. After this change the body comes from `ExceptionRenderer`. The roadrunner `WorkerRequestHandlerTest` only checks the status, so it still passes once `FakeRouteMatcher::allowedMethods()` returns `[]`. **Fix:** listed these in task 005.
4. **Task 005: edge cases are missing.** Nothing covers HEAD on an unknown path (the rendered 404 body must be stripped), OPTIONS on an unknown path (should be 404, not an automatic 204), HEAD to a POST-only path (405 with `Allow: POST, OPTIONS`), or route middleware being skipped for unmatched requests. **Fix:** added requirements.
5. **Task 004: method names are not pinned, and the stream cleanup is missing.** Task 005 depends on 004's API, but "reports whether the body was omitted" has no method name. `StreamingResponse::send()` currently acquires a connection-limiter slot and closes the stream in `finally`. The HEAD path must not acquire a slot and must still call `$this->stream->close()`. **Fix:** pinned `withoutBody(): static` (`#[NoDiscard]`) and `isBodyOmitted(): bool`, and added the requirements.
6. **Task 007: the `paths` contract is undefined.** The task does not say how patterns match, what the default is, or which env var sets it. `CorsConfig` uses `getArray()`, and `FakeConfigRepository` throws `ConfigNotFoundException` on missing keys. So every hand-built CorsConfig breaks: `packages/cors/tests/Helpers.php::createCorsConfig()` and `packages/security/tests/Unit/CsrfHttpMappingTest.php:54-61`. **Fix:** proposed a contract: default `['*']`, `CORS_PATHS`, `*` wildcard against `Request::path()`. Listed both test sites.
7. **Task 007: "outermost" needs an explicit `sequence.before` list.** Global middleware order is module load order, and the first entry is outermost. The modules that declare `globalMiddleware` are page-cache, session-file, session-database, authentication, authorization and layout. CORS has to wrap page-cache so cached hits get CORS headers. **Fix:** listed them in task 007.
8. **Task 007: the preflight omits `Access-Control-Allow-Credentials`.** `CorsMiddleware::handle()` only adds that header to actual responses. Credentialed preflights fail in browsers, and this becomes visible once CORS is global and handles real preflights. **Fix:** added a requirement.
9. **Task 002: the `allowedMethods()` contract only lives in the implementation.** Task 005 builds the `Allow` header from it, and the 9 test doubles must implement it. **Fix:** wrote down the order (GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS, then others alphabetically), when HEAD and OPTIONS are added, the empty-array meaning, and the memo eviction policy (clear at 1,000).

## Minor (Nice to address)

- `Request::method()` returns `REQUEST_METHOD` as-is, not uppercased. A lowercase `head` will not hit the HEAD fallback. This is existing behaviour.
- page-cache config allows `HEAD`. With HEAD falling back to GET, `#[Cacheable]` GET routes will also store a HEAD entry with the full body, because stripping happens outside the pipeline. The entry is harmless because the cache key includes the method, but it doubles storage.
- An explicit dynamic HEAD route beats a static GET route through fallback (`HEAD /shows/live` with `#[Head('/shows/{id}')]` plus `#[Get('/shows/live')]`). This is defensible but undocumented.
- Global CORS will throw `CorsException` (a 500) on every cross-origin request when credentials are on and the origin is `*`. This was already true for route-level use, but its reach is now global.
- CORS `Vary: Origin` replaces any existing `Vary` value because `withHeaders()` overwrites.
- Apps that also attach `CorsMiddleware` at route level will run it twice. The headers are idempotent, but docs (task 009) should say to remove the route-level use.

## Questions for the Team

- Session middleware (especially session-database) will now run on every 404 from scanners and bots, which can mean DB writes per bot hit. Is that acceptable, or should session start lazily?
- Should `route:list` show the implicit HEAD (from GET) and automatic OPTIONS rows, or only declared routes?
- Is the default for `cors.paths` `['*']` (applies wherever origins are configured) or Laravel-style `['api/*']`?
