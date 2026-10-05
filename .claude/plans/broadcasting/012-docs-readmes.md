# Task 012: Docs pages and slim READMEs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 007, 008, 009, 010, 011
**Retry count**: 0

## Description
docs-markdown pages broadcasting.md, broadcasting-mercure.md (FrankenPHP hub), broadcasting-pusher.md (Pusher/Soketi/Reverb + Echo snippet), sse.md update ("When to use SSE vs. broadcasting", max_connections), testing docs for FakeBroadcaster, real-time guide link; slim READMEs per DOCS-STANDARDS.

## Requirements (Test Descriptions)
- [x] `each new package README has Installation, Quick Example and Documentation sections`
- [x] `docs-markdown tests pass with the new pages`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
