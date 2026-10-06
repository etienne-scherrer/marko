# Task 007: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Document prompting and `--no-interaction` in core.md's console section, FakeConfirmationPrompter in testing.md, and update database.md / devai.md / mcp.md where they mention confirmation or `--no-interaction`.

## Context
- Related files: packages/docs-markdown/docs/packages/{core,testing,database,devai}.md, packages/docs-markdown/docs/ai-assisted-development/mcp.md, docs/DOCS-STANDARDS.md
- When documenting how to override the prompter: use a module binding or a Preference replacing `ConfirmationPrompterInterface`. Don't say a Preference on `StdinConfirmationPrompter` works (see _plan.md Architecture Notes).

## Requirements (Test Descriptions)
- [ ] `core.md documents ConfirmationPrompterInterface and --no-interaction`
- [ ] `testing.md documents FakeConfirmationPrompter`
- [ ] `database.md and devai.md reference the core prompter`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
(Left blank - filled in by programmer during implementation)
