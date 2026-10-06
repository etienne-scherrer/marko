# Task 004: FakeConfirmationPrompter in marko/testing

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
A scripted fake of the core prompter that records the questions asked, with assertions.

## Context
- Related files: packages/testing/src/Fake/FakeConfirmationPrompter.php (new), packages/testing/tests
- Patterns to follow: FakeClock / FakeMailer assertion style (AssertionFailedException)
- Constructor: `array $answers = [], bool $interactive = true`; public `$asked` list of question strings

## Requirements (Test Descriptions)
- [ ] `it returns scripted answers in order`
- [ ] `it records each question asked`
- [ ] `it returns the default without consuming an answer when not interactive`
- [ ] `it throws loudly when asked more questions than answers were scripted`
- [ ] `it asserts a question was asked and fails when it was not`
- [ ] `it asserts nothing was asked and fails when something was`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
