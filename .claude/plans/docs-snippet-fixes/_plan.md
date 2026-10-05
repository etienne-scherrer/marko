# Plan: Docs Snippet Fixes

## Created
2026-10-05

## Status
completed

## Objective
Fix README and docs snippets that reference classes or config layouts that do not exist, and add a guard test that fails when a fenced PHP snippet references a `Marko\...` class that does not resolve through the packages' PSR-4 maps.

## Related Issues
Closes #183

## Discovery Notes
- A prototype scan of 232 Markdown files (1,237 `Marko\...` references in fenced PHP blocks) reproduced exactly the issue's findings: `Marko\Http\Request`/`Response` in the skeleton README, `Marko\Container\Contracts\ContainerInterface` in the mail-log docs, and the fictional `Marko\Analytics\` / `Marko\Commerce\` example namespaces.
- `DatabaseConfig::validateConfigArray()` requires a flat array with `driver`, `host`, `port`, `database`, `username`, `password`. Nothing reads `schema`, `charset`, or `collation`.
- `Marko\Core\Container\ContainerInterface` has no `bind()`, so the mail-log "conditional binding" snippet could not work with a corrected import alone; it is rewritten as a closure binding (the pattern `marko/mail-smtp`'s own `module.php` uses).
- Docs pages on develop use `$_ENV['X'] ?? default` in config files (not `env()`), so the READMEs match that style.
- The marko/testing expectation-loading text is owned by #179 and left untouched.

## Scope

### In Scope
- Skeleton README controller example (real classes plus `#[Get('/')]`)
- database-pgsql and database-mysql READMEs: flat config layout with `port`, note pointing to `marko/database-readwrite`
- Remove the unread `schema` key (and its "Schema" section) from the pgsql docs page; remove unread `charset`/`collation` keys from the mysql README and docs page
- mail-log docs: correct `ContainerInterface` namespace and a snippet that works
- `.claude/project-overview.md`: `ratelimiter`
- Move fictional examples to non-Marko namespaces (`Acme\Analytics`, `App\Commerce`) so the guard needs no allowlist entries
- `tests/DocsClassReferenceTest.php` guard plus fixtures

### Out of Scope
- Wiring a `schema` config key through to PostgreSQL (`search_path`)
- marko/testing expectation-loading docs (#179)
- The optional `config/database.php` required-keys check

## Success Criteria
- [x] Guard passes on the fixed tree and fails with file:line when `use Marko\Nope\Thing;` is added to a README
- [x] All exit criteria in #183 addressed
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Docs class-reference guard test | - | completed |
| 002 | Fix wrong snippets in READMEs and docs | - | completed |

## Architecture Notes
The scanner lives in `tests/Support/DocsClassReference/` and is loaded with `require_once`, matching the existing `Psr7Containment` support classes.

## Risks & Mitigations
- Snippets added by other in-flight tickets may fail the guard once this merges: intended, and the failure names file, line, and class.
