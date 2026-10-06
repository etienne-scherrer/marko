# Task 007: Docs page and README

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Update authorization.md 'Cost on Routes Without #[Can]' to describe when misconfiguration is reported, and check the README.

## Context
- Related files: packages/docs-markdown/docs/packages/authorization.md, packages/authorization/README.md
- Patterns to follow: existing authorization package classes; DiscoveryCacheContributorInterface implementations

## Requirements (Test Descriptions)
- [x] `docs describe live boot and discovery:cache validation`
- [x] `docs describe the remaining request-time behaviour on cached boots`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
authorization.md "Cost on Routes Without #[Can]" rewritten; API reference adds CanAttributeReader and AuthorizationConfigurationException; core.md documents ApplicationBooted. README unchanged (the slim pointer is still accurate).
