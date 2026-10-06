# Plan: Validation Wildcard Rules

## Created
2026-10-05

## Status
completed

## Objective
Add wildcard keys (`photos.*`, `items.*.name`, `matrix.*.*`) to `marko/validation` so rules apply to every item of an array, and make an unresolvable wildcard fail loudly instead of silently passing.

## Related Issues
Closes #251

## Discovery Notes
- `Validator::validate()` treats every rules key as a literal path; `getValue()` looks up a literal `*` key, gets `null`, and every non-required rule is skipped, so `photos.*` silently passes.
- `Confirmed`, `Same` and `Different` read the other field with a flat `$data[$field]`.
- `RuleParser` builds rule instances per key; `RuleInterface::passes($field, $value, $data)` must not change.
- `TestClient` (marko/testing) sends `photos[]` uploads as a list under `photos`. Its fixture app (`packages/testing/tests/fixtures/http-app`) discovers modules from `vendor/marko/*` stubs, and `ValidationException` renders as 422 through the routing pipeline.
- Decisions taken from the issue's recommendations: `*` is always a wildcard (as in Laravel); cross-field rules take absolute dot paths, and a `*` in the other field is replaced by the current row's index.

## Scope

### In Scope
- Wildcard expansion in `Validator` against the data, errors keyed by concrete path
- Absent/empty parent expands to nothing; a scalar at a wildcard position fails loudly
- Shared dot-path lookup (`DataPath`) used by the validator and the cross-field rules
- `same`/`different` substitute `*` in the other field with the current row indexes (new optional `WildcardAwareRuleInterface`)
- Routing-level multi-file upload test through `TestClient` returning 422 with per-file keys
- Docs page + README update

### Out of Scope
- Relative (row-local) field references in cross-field rules
- Escaping a literal `*` key
- Changing `RuleInterface`

## Success Criteria
- [x] `photos.*` with `image|max_size` validates each file, errors keyed `photos.0`, `photos.1`
- [x] `items.*.name` and `matrix.*.*` expand correctly
- [x] Wildcard over absent/empty parent produces no errors; parent `required|array` still fails
- [x] Scalar at a wildcard segment fails loudly
- [x] Regression: wildcard rule no longer silently passes invalid items
- [x] `confirmed`/`same`/`different` resolve dot paths inside and outside wildcard rows
- [x] TestClient multi-file upload returns 422 with per-file error keys
- [x] Docs page documents wildcard keys and replaces the "not supported yet" note
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Wildcard expansion in Validator | - | completed |
| 002 | Dot-path and wildcard-aware cross-field rules | 001 | completed |
| 003 | TestClient multi-file upload 422 test | 001 | completed |
| 004 | Docs page and README | 001, 002, 003 | completed |

## Architecture Notes
- `DataPath::get()` is the single dot-path lookup, shared by `Validator` and the cross-field rules.
- Expansion walks the rule key's segments against the data. A literal segment descends (or yields `null`). A `*` segment iterates an array, yields nothing for `null`/`''`/`[]`, and for any other value (scalars and objects such as a single `UploadedFile`) records an error keyed by the path reached, one per (path, rule key).
- Rules are parsed once per rule key **before** expansion, so unknown rules throw even when a wildcard matches nothing.
- `Validator::expand()` returns `array<string, list<int|string>>` (concrete key → matched wildcard indexes). This is the contract Task 002 builds on.
- `WildcardAwareRuleInterface::forWildcardIndexes(array $indexes): RuleInterface` lets a rule rewrite its own `*` references for a row without changing `RuleInterface`.

## Risks & Mitigations
- Field names that contain a literal `*`: documented as always being a wildcard.
- A cross-field reference with more `*` than the rule key: throws `InvalidArgumentException` instead of comparing against nothing.
