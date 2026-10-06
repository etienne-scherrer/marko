# Plan: Core Confirmation Prompter

## Created
2026-10-05

## Status
completed

## Objective
Replace the two package-local "ask the operator yes/no" abstractions (marko/database and marko/devai) with one `Marko\Core\Command\ConfirmationPrompterInterface` in core, handle `--no-interaction` once in core, and ship a `FakeConfirmationPrompter` in marko/testing.

## Related Issues
Closes #225
Relates to #235

## Discovery Notes
- `Marko\Database\Command\ConfirmationPrompterInterface` (`confirm(): bool`, command writes the question) + `StdinConfirmationPrompter`, bound in `packages/database/module.php`; used by `MigrateCommand::confirmDestructiveChanges()`.
- `Marko\DevAi\Process\ConfirmationPrompterInterface` (`confirm(string, bool)`) + `StdinPrompter` (writes to raw STDOUT, `noInteraction` constructor flag never set by anything), bound in `packages/devai/module.php`; used by `InstallCommand::maybeInstallDocsDriver()`, which also reads its own `no-interaction` flag.
- Core console: `Input` (readonly, argv parsing, `withFlags()`), `Output` (stream writer), `CommandRunner::run(name, Input, Output)` resolves the command from the container. Core has no module.php; `Application::initialize()` registers core services directly on the container before module bindings are registered by `BindingRegistry` (which only tracks module-to-module conflicts, so a module binding overrides a core default).
- The prompter needs the running command's Input (for `--no-interaction`) and Output (to write the question). `CommandRunner` is the one place that holds both, so it registers them as container instances for the run before resolving the command; `StdinConfirmationPrompter` autowires them.
- `marko/testing` fakes throw `AssertionFailedException` from `assert*()` methods.

## Scope

### In Scope
- `Input::isInteractive()`; `--no-interaction` declared as a global flag by `CommandRunner` for every command
- `CommandRunner` registers the running command's `Input` and `Output` in the container
- `Marko\Core\Command\ConfirmationPrompterInterface` + `StdinConfirmationPrompter`; default binding in `Application`
- `Marko\Testing\Fake\FakeConfirmationPrompter`
- Migrate `MigrateCommand` and devai `InstallCommand`; delete both package-local interfaces, implementations, bindings and their tests
- Docs: core.md (console prompting + `--no-interaction`), testing.md, database.md, devai.md (and mcp.md if it mentions the flag)

### Out of Scope
- Staging protection (#235)
- Short `-n` alias for `--no-interaction` (could collide with existing short options)
- Re-asking on unrecognised answers
- Deprecated aliases for the removed interfaces (signatures are incompatible; this is labelled breaking)

## Success Criteria
- [ ] Core interface + stdin implementation with tests for y/yes/n/no/empty/default and non-TTY
- [ ] `--no-interaction` handled once in core and makes `isInteractive()` false for every command
- [ ] MigrateCommand and InstallCommand use the core interface; old interfaces/impls/bindings deleted
- [ ] FakeConfirmationPrompter documented in testing.md
- [ ] core.md, database.md, devai.md updated
- [ ] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Global `--no-interaction` and per-run Input/Output in CommandRunner | - | completed |
| 002 | Core ConfirmationPrompterInterface + StdinConfirmationPrompter | 001 | completed |
| 003 | Default binding in Application | 002 | completed |
| 004 | FakeConfirmationPrompter in marko/testing | 002 | completed |
| 005 | Migrate db:migrate to the core prompter | 002, 004 | completed |
| 006 | Migrate devai:install to the core prompter | 002, 004 | completed |
| 007 | Documentation | 001-006 | completed |

## Architecture Notes
- Contract: the prompter writes the question and a `[Y/n]`/`[y/N]` hint through the command's `Output`; `y`/`yes`/`n`/`no` in any case; empty input, EOF or an unrecognised answer returns `$default`. When not interactive, `confirm()` asks nothing, reads nothing and returns `$default`. Commands that must not proceed without a human (destructive actions) check `isInteractive()` first and refuse loudly.
- Non-interactive = `--no-interaction` passed, or standard input is not a TTY.
- Interface: `isInteractive(): bool` and `confirm(string $question, bool $default = false): bool` (pinned in task 002).
- Default binding registered with `$container->bind()` in `Application` before module bindings, so a module binding or a Preference replacing `ConfirmationPrompterInterface` overrides it. A Preference on `StdinConfirmationPrompter` is NOT applied: `Container::resolve()` checks preferences only for the requested id, not for the binding target.
- `Input` can't be autowired, so the prompter only resolves inside `CommandRunner::run()` (after Input and Output are registered). Resolving it elsewhere throws `BindingException`.

## Risks & Mitigations
- Breaking change for anyone who bound their own package-local prompter: label PR `breaking`, note in PR body.
- Registering Input/Output on the container mutates container state per run: nested runs re-register; commands already resolved keep their prompter. Documented.
- `Application.php` is also touched by other work: keep the hunk small.
