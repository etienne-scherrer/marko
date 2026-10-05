# Task 007: CORS global registration, paths, expose headers, preflight detection

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Register `Marko\Cors\Middleware\CorsMiddleware` as outermost global middleware, add a `paths` config key, emit `Access-Control-Expose-Headers`, and only short-circuit real preflights (OPTIONS + `Access-Control-Request-Method`).

## Context
- Related files: packages/cors/module.php, config/cors.php, src/Config/CorsConfig.php, src/Middleware/CorsMiddleware.php, tests/
- Outermost: global middleware order = module load order (first = outermost). module.php needs `'sequence' => ['before' => ['marko/page-cache', 'marko/session-file', 'marko/session-database', 'marko/authentication', 'marko/authorization', 'marko/layout']]` (every module declaring `globalMiddleware`). CORS must wrap page-cache so cached hits get CORS headers.
- `paths` contract (proposed; confirm in review): `CorsConfig::paths(): array<string>` reads `cors.paths`; default in packages/cors/config/cors.php is `explode(',', $_ENV['CORS_PATHS'] ?? '*')`. Each pattern matches `Request::path()` (leading slash stripped) where `*` matches any characters including `/` (e.g. `api/*`, `*`). Non-matching requests pass straight to `$next`.
- Adding `cors.paths` breaks every hand-built CorsConfig because `FakeConfigRepository` throws on missing keys: update packages/cors/tests/Helpers.php `createCorsConfig()` (add `$paths = ['*']`) and packages/security/tests/Unit/CsrfHttpMappingTest.php:54-61.
- `Access-Control-Expose-Headers` only when `exposeHeaders()` is non-empty.
- Preflight must also send `Access-Control-Allow-Credentials: true` when `supportsCredentials()` (currently only actual responses get it; credentialed preflights fail in browsers).

## Requirements (Test Descriptions)
- [x] `it runs before every other module that declares global middleware`
- [x] `it sends Access-Control-Allow-Credentials on preflight when credentials are supported`
- [x] `it omits Access-Control-Expose-Headers when none are configured`
- [x] `it registers CorsMiddleware as global middleware`
- [x] `it skips requests whose path is outside the configured paths`
- [x] `it emits Access-Control-Expose-Headers on actual requests`
- [x] `it passes OPTIONS without Access-Control-Request-Method to the next handler`
- [x] `it answers a cross-origin preflight to a POST-only route with 204 and CORS headers through the router`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
