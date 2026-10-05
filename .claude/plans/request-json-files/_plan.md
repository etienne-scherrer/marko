# Plan: Request JSON Body and Uploaded Files

## Created
2026-10-05

## Status
completed

## Objective
Give `Marko\Routing\Http\Request` first-class JSON body parsing (`isJson`, `wantsJson`, `json`, `input`) and uploaded-file support (`UploadedFile`, `file`, `files`, `hasFile`), bind JSON fields to typed controller parameters, and map PSR-7 uploads through the RoadRunner bridge instead of refusing them.

## Related Issues
Closes #174

## Discovery Notes
- `Request` is a `readonly class`; `fromGlobals()` only parses form-urlencoded PUT/PATCH/DELETE bodies and never reads `$_FILES`. `withRoute()` rebuilds via `new self(...)`, dropping anything not passed.
- `Router::resolveParameters()` resolves route params > `post()` > `query()` > default; JSON bodies never bind.
- `Psr7RequestBridge` throws `UploadedFilesNotSupportedException` for any upload; `roadrunner.md` and `DocsTest` document that limitation.
- #169 (exception → HTTP mapping / `HttpExceptionInterface`) is NOT merged, so `MalformedJsonException` cannot implement it yet. The Router maps it to 400 on the parameter-binding path (same as `InvalidRouteParameterException`).
- `marko/media` has its own `Marko\Media\Value\UploadedFile` value object; the new routing `UploadedFile` is the HTTP-layer object. Bridging the two is out of scope.

## Scope

### In Scope
- `Request::isJson()`, `wantsJson()`, `json()` (dot-notation), `input()`; JSON decoded once in the constructor, errors deferred to access time as `MalformedJsonException`.
- `Marko\Routing\Http\UploadedFile` with `moveTo()`, `isValid()`, `isMoved()`, `stream()`, `contents()`, `mimeType()`, `guessExtension()` and loud `UploadedFileException`s.
- `Request` `files` constructor param, `file()`, `files()`, `hasFile()`, `$_FILES` normalization (single, `files[]`, nested), carried through `withRoute()`.
- Router binds JSON fields via `input()`; malformed JSON during binding → 400.
- RoadRunner bridge maps PSR-7 uploaded files; temp files it creates are removed after each request; `UploadedFilesNotSupportedException` deleted.
- Docs: `routing.md` Request API reference, `roadrunner.md` limitation removed, READMEs checked.

### Out of Scope
- Validation rules for files (`file`, `max_size`, `mimes`) — follow-up per ticket.
- `HttpExceptionInterface` on `MalformedJsonException` (#169 not merged).
- Parsing `multipart/form-data` for PUT/PATCH under FPM (`request_parse_body()`).
- HTTP test client (#180).

## Success Criteria
- [x] Every exit criterion in #174 has a passing test
- [x] All tests passing
- [x] `composer ci` green with zero PHPStan errors
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Request JSON support + MalformedJsonException | - | completed |
| 002 | UploadedFile + UploadedFileException | - | completed |
| 003 | Request files + `$_FILES` normalization + withRoute | 001, 002 | completed |
| 004 | Router binds JSON body fields | 001 | completed |
| 005 | RoadRunner bridge maps uploaded files | 002, 003 | completed |
| 006 | Docs and READMEs | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- JSON is decoded eagerly in the constructor (body is already fully read; keeps the class readonly) but a decode failure is stored and only thrown when the JSON payload is accessed, so a malformed body surfaces inside the router/middleware where it can map to 400 instead of crashing the front controller.
- `withRoute()` uses PHP 8.5 `clone()` with properties so files and the decoded JSON are carried through without re-decoding.
- `UploadedFile` is a plain class; only `$moved` is mutable. `moveTo()` uses `move_uploaded_file()` under a web SAPI and `rename()` otherwise.
- `json()` reads the body only when `isJson()`; for other content types it returns `[]` / the default (never tries to decode a form body).

## Risks & Mitigations
- Temp files leaking in a long-running worker: the bridge tracks the temp files it writes and `WorkerRequestHandler` removes them after every request.
- Behaviour change in parameter binding: priority stays route > body > query > default; form requests behave exactly as before.
