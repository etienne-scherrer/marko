# Task 002: Core ConfirmationPrompterInterface and StdinConfirmationPrompter

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add the single console confirmation contract to core and its standard-input implementation.

## Context
- Related files: packages/core/src/Command/ConfirmationPrompterInterface.php (new), packages/core/src/Command/StdinConfirmationPrompter.php (new)
- Replaces packages/database/src/Command/StdinConfirmationPrompter.php and packages/devai/src/Process/StdinPrompter.php
- Constructor: `Input $input, Output $output, mixed $stream = null` (stream defaults to STDIN)
- Interface signature (fixed contract; tasks 004/005/006 build against it in parallel):
  ```php
  interface ConfirmationPrompterInterface
  {
      /** False when --no-interaction was passed or standard input is not a TTY. */
      public function isInteractive(): bool;

      /** Writes "$question [y/N] " / "$question [Y/n] " through Output, reads one line; returns $default when not interactive. */
      public function confirm(string $question, bool $default = false): bool;
  }
  ```
- `isInteractive()` = `$input->isInteractive() && stream_isatty($stream)`

## Requirements (Test Descriptions)
- [ ] `it writes the question with a [y/N] hint when the default is no`
- [ ] `it writes the question with a [Y/n] hint when the default is yes`
- [ ] `it confirms on y or yes in any case`
- [ ] `it declines on n or no in any case`
- [ ] `it returns the default on empty input, end of input or an unrecognised answer`
- [ ] `it is not interactive when standard input is not a terminal`
- [ ] `it is not interactive when --no-interaction is passed`
- [ ] `it returns the default without asking or reading when not interactive`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
