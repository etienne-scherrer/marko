# Task 007: Benchmark script

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
`bin/benchmark-discovery-cache.php` generates a project with 50 app modules / 600 classes (controllers + plain classes), then times `Application::boot()` + a 404 dispatch in fresh PHP processes (opcache file cache, warmed) uncached vs cached, prints medians and the speed-up, and exits non-zero below 3x.

## Context
- Generated project's vendor: symlink `vendor/marko/*` to `packages/*` and point `vendor/autoload.php` at the root autoloader (same approach as `tests/Integration/App/Helpers.php`); no installed.json needed (fingerprint marker).
- Options: `--modules`, `--classes`, `--runs`, `--min-ratio` (defaults 50/600/…/3). Detect opcache; when unavailable, warn and continue without it.
- Tests (under `tests/`) must be fast: tiny fixture (e.g. 2 modules/10 classes, 1 run) with `--min-ratio=0` for the print test and `--min-ratio=1000` for the failure test. Clean up temp dirs without following vendor symlinks.

## Requirements (Test Descriptions)
- [x] `it prints uncached and cached medians and the ratio`
- [x] `it fails when cached is less than 3x faster`

## Acceptance Criteria
- Script runs locally; numbers recorded in the PR

## Implementation Notes
(Left blank - filled in by programmer during implementation)
