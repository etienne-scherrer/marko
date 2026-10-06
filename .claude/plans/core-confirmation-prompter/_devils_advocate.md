# Devil's Advocate Review: core-confirmation-prompter

## Critical (Must fix before building)

1. **Task 001 - flag ordering in `CommandRunner::run()`.** Today the runner resolves the command first (line 35) and calls `withFlags()` afterwards, and only when `$definition->flags !== []`. To register the Input the command will run with, the runner has to apply `withFlags([...$definition->flags, 'no-interaction'])` (deduplicated, and always, not only when the definition has flags), then call `instance(Input::class, $input)` and `instance(Output::class, $output)`, then resolve the command, then pass that same `$input` to `execute()`. If it doesn't, `StdinConfirmationPrompter` autowires a different Input than the one the command receives, or (on the first run) can't be autowired at all.
2. **Tasks 002/004/005/006 - interface signature is never pinned.** Workers on 004, 005 and 006 run in parallel and need the exact signature. Pin `isInteractive(): bool` and `confirm(string $question, bool $default = false): bool` in task 002.

## Important (Should fix before building)

3. **Task 003 - `Input` can't be autowired outside a command run.** `Input::__construct(array $arguments, ...)` has a builtin parameter with no default, so `get(ConfirmationPrompterInterface::class)` before `CommandRunner` registers an Input throws `BindingException::unresolvableParameter('arguments', Input::class)`. A test like "binds ... by default" that just resolves the interface will fail. The test should register Input and Output instances first, or check the class it resolves to after doing so.
4. **Plan Architecture Notes - "a Preference on `StdinConfirmationPrompter` replaces it" is wrong.** `Container::resolve()` checks preferences only for the id that was requested (the interface). After following the binding to `StdinConfirmationPrompter`, it builds that class without checking preferences again. A Preference has to replace `ConfirmationPrompterInterface`, or a module has to bind it. Task 007's docs must not repeat the wrong claim.
5. **Task 005 - the question would be printed twice.** `MigrateCommand::confirmDestructiveChanges()` writes `'Generate a migration with these changes? [y/N] '` itself, and the core prompter now writes the question and the hint too. Remove the `$output->write()` and call `confirm('Generate a migration with these changes?', false)`. The non-interactive check stays on `$this->confirmationPrompter->isInteractive()`, which covers both the TTY and the flag, not on `$input->isInteractive()`.
6. **Task 006 - existing tests depend on the flag.** `InstallCommandTest` line 198 asserts that the flags include `no-interaction`. The tests at lines 247, 368 and 388 call `execute()` with `--no-interaction` and an interactive fake. Once the command stops reading the flag, those tests have to use `FakeConfirmationPrompter(interactive: false)`, or they will try to prompt. Use the prompter's `isInteractive()` only.

## Minor (Nice to address)

- `MarkoConsoleDispatcher` (mcp) runs commands while stdin is the JSON-RPC pipe. The TTY check makes the prompter non-interactive there, so it never reads protocol bytes. A regression test would lock that in.
- In the long-running MCP server, Input and Output stay registered in the container after a dispatch, pointing at a closed memory stream. That's harmless because commands aren't shared, but it's worth a comment.
- `STDIN` is undefined outside the CLI SAPI. Guard the default stream with `defined('STDIN')`, otherwise treat the prompter as non-interactive.

## Questions for the Team

- mcp.md says that with `--no-interaction` "a hint is printed instead", but `InstallCommand` returns silently. Should task 006 print the hint, or task 007 fix the doc?
