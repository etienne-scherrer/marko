# Task 002: PublicSuffixList

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
A small built-in public suffix list for the test client: decides whether a domain is a public suffix and computes a host's site (registrable domain) for same-site checks.

## Context
- Related files: packages/testing/src/Http/PublicSuffixList.php (new), packages/testing/tests/Unit/Http/PublicSuffixListTest.php (new)
- Patterns to follow: static helpers on JarCookie

## Requirements (Test Descriptions)
- [x] `it treats every single-label domain as a public suffix`
- [x] `it treats common multi-label suffixes such as co.uk as public suffixes`
- [x] `it does not treat a registrable domain as a public suffix`
- [x] `it never treats an IP address as a public suffix`
- [x] `it returns the registrable domain as the site of a host`
- [x] `it returns the host itself as the site of a single-label host or an IP address`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Single-label domains follow the PSL default `*` rule. The multi-label list covers common ccSLDs and shared-hosting suffixes; the limitation is documented.
