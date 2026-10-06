# Task 006: Register rules in RuleParser

**Status**: completed
**Depends on**: 002, 003, 004, 005
**Retry count**: 0

## Description
Register `file`, `image`, `mimes`, `mimetypes`, `max_size` and `min_size`, and validate files end to end through `Validator`.

## Context
- Related files: packages/validation/src/Validation/RuleParser.php

## Requirements (Test Descriptions)
- [x] `it parses each file rule name`
- [x] `it validates an uploaded file with string rules`
- [x] `it reports a too-large file under its field`
- [x] `it throws when max_size or min_size has no numeric parameter`
- [x] `it throws when mimes or mimetypes has no entries`
- [x] `it skips an absent optional file and reports a missing required file`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Rule strings and classes are pinned in "Shared Contracts" in `_plan.md`.
- `max_size`/`min_size`: throw `InvalidArgumentException` naming the rule when the parameter is missing or not numeric. Do not default to 0 like `min`/`max` do.
- `mimes`/`mimetypes`: trim the parameters and drop blanks (`mimes:` yields `['']`), then spread them into the constructor, which throws on an empty list.
- An absent file is `null` in the data (Request drops `UPLOAD_ERR_NO_FILE` inputs), so `Validator` already skips it unless `required` is present. Cover this with a test; no Validator change is expected.
