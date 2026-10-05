# Plan: HTTP Exception Mapping

## Created
2026-10-05

## Status
completed

## Objective
Turn exceptions that carry an HTTP meaning into correct HTTP responses inside the routing pipeline (so outer middleware still decorates them), and make `marko/errors-advanced` resolvable, production-safe, and status-correct.

## Related Issues
Closes #169

## Discovery Notes
- `Router::handle()` hand-maps only `InvalidRouteParameterException` → 400; everything else escapes the pipeline to the registered `ErrorHandlerInterface` (always 500 HTML).
- `MiddlewarePipeline` is built by `Router` via `new MiddlewarePipeline($container)`; middleware is resolved lazily from the container, so the renderer can be resolved the same way (preference-aware, zero cost on the happy path).
- `AdvancedErrorHandler`'s `?FormatterInterface` parameter cannot be autowired (container only uses defaults for builtin types); its fallback `PrettyHtmlFormatter` defaults to `'development'` → debug-page leak in production once resolvable. It also never sets 500 nor clears buffers.
- `ValidationException` extends `\Exception` directly; `marko/validation` requires only php.
- `RepositoryException::entityNotFound()` returns the generic class.
- Follow-up #171 throws `HttpException::notFound()` / `methodNotAllowed($allowed)` from an unmatched-route terminal handler run through the pipeline, so the pipeline must render exceptions thrown by the terminal handler and the `Allow` header must be caller-specified verbatim.

## Scope

### In Scope
- `Marko\Core\Exceptions\HttpExceptionInterface`
- `Marko\Routing\Exceptions\HttpException` with named constructors, `Marko\Routing\Http\HttpStatus` reason phrases
- `Marko\Routing\Http\ExceptionRenderer` (JSON/HTML negotiation, Preference-able)
- Rendering in `MiddlewarePipeline::process()` at the depth the exception is thrown
- `InvalidRouteParameterException` (400), `ValidationException` (422), `CsrfTokenMismatchException` (419), new `EntityNotFoundException` (404)
- errors-simple / errors-advanced: JSON negotiation, last-resort HTTP status mapping, advanced handler resolvable + production-safe + status 500 + buffer clearing
- Docs: routing, errors-simple, errors-advanced, validation, security, database

### Out of Scope
- Redirect-back-with-errors for HTML forms; templated error pages via `marko/view`
- 404/405/HEAD/OPTIONS router changes (#171)
- `abort()` helper, facades

## Success Criteria
- [x] Every exit criterion in #169 has a test
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | HttpExceptionInterface + HttpException + HttpStatus | - | completed |
| 002 | ExceptionRenderer | 001 | completed |
| 003 | Pipeline rendering + Router/InvalidRouteParameterException | 001, 002 | completed |
| 004 | ValidationException → 422 | 001, 002 | completed |
| 005 | CsrfTokenMismatchException → 419 + outer middleware decoration | 001, 003 | completed |
| 006 | EntityNotFoundException → 404 | 001, 002 | completed |
| 007 | errors-simple JSON negotiation + last-resort mapping | 001 | completed |
| 008 | errors-advanced resolvable, production-safe, 500 | 007 | completed |
| 009 | Docs and READMEs | 001-008 | completed |

## Architecture Notes
- Interface in core carries no HTTP behavior (status, headers, client-safe data).
- The renderer only ever reads `getResponseData()` — never `getMessage()` — so each exception explicitly opts in to what the client sees (entity class/ID never leak).
- Renderer resolved once from the container by `RoutingBootstrapper` (Preference-aware) and injected into `Router` → `MiddlewarePipeline`; both default to `new ExceptionRenderer()` for direct construction (lazy container lookup would break the many mock-container tests).
- `AdvancedErrorHandler` is bound with a closure in module.php (passes `Environment`); its fallback `PrettyHtmlFormatter` is built for the real environment.
- Non-HTTP throwables are never caught by the pipeline.

## Risks & Mitigations
- Breaking `new AdvancedErrorHandler()` call sites: keep `Environment` optional for direct construction, bind via closure in module.php.
- Mock containers in existing pipeline tests: renderer resolved only when an HTTP exception is caught.
