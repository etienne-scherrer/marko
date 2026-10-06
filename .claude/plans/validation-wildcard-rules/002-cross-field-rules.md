# Task 002: Dot-path and wildcard-aware cross-field rules

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make `confirmed`, `same` and `different` resolve their other field by dot path via `DataPath`, and let `same`/`different` replace `*` in the other field with the current row's indexes.

## Context
- Related files: packages/validation/src/Rules/{Confirmed,Same,Different}.php, new packages/validation/src/Contracts/WildcardAwareRuleInterface.php, Validator.php
- `RuleInterface` stays unchanged.

## Requirements (Test Descriptions)
- [x] `it resolves confirmed against a nested dot path`
- [x] `it resolves confirmed inside a wildcard row`
- [x] `it resolves same and different against absolute dot paths`
- [x] `it replaces the wildcard in same with the current row index`
- [x] `it replaces the wildcard in different with the current row index`
- [x] `it throws when the other field has more wildcards than the rule key`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
New `WildcardAwareRuleInterface::forWildcardIndexes(array $indexes): RuleInterface`, implemented by `Same` and `Different`. The validator calls it for every key (with `[]` for plain keys) so an unmatched `*` fails loudly everywhere.

- Indexes come from Task 001's `expand()` map (`concrete key => list<int|string>`). Document `@param list<int|string> $indexes` for PHPStan, since keys can be strings for associative data.
- Replace each `*` in the other field, left to right, with the next index. Throw `InvalidArgumentException` when the other field has more `*` than there are indexes, and name the rule key and the other field in the message.
- `Same`/`Different` are `readonly`: return a new instance, never mutate. The rewritten instance's `message()` must name the resolved field (`The items.0.password field must match the items.0.password_confirmation field.`), never the `*` form. Add this assertion to the "replaces the wildcard" tests.
- `Confirmed` needs no interface: it already receives the concrete key as `$field`, so `DataPath::get($data, $field . '_confirmation')` resolves inside rows.
