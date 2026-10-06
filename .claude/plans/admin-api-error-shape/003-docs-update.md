# Task 003: Docs and Tutorial Update

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make the admin-api docs describe one error shape and stop pointing at the removed helpers.

## Context
- packages/docs-markdown/docs/packages/admin-api.md (intro, Available Endpoints, Using ApiResponse in Custom Endpoints, API reference)
- packages/docs-markdown/docs/tutorials/build-an-admin-panel.md (ApiResponse table)
- packages/admin-api/README.md (slim pointer; check it does not reference removed helpers)

## Requirements (Test Descriptions)
- [x] `docs intro describes {data, meta} for success and {message} for errors`
- [x] `docs link to the routing errors section`
- [x] `custom endpoint example throws HttpException::notFound`
- [x] `API reference lists only success, created and paginated`
- [x] `tutorial table lists only the remaining helpers and points to HttpException`

## Acceptance Criteria
- No docs reference the removed helpers

## Implementation Notes
