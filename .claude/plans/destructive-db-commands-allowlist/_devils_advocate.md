# Devil's Advocate Review: destructive-db-commands-allowlist

## Critical (Must fix before building)

1. **Integration test asserts the old messages (tasks 003, 005).** `tests/Integration/App/MigrationSafetyTest.php:109,111` (group `integration-services`) asserts `'Rebuild cannot be run in production'` and `'Seeders cannot be run in production'`. The new guard prints `Error: db:rebuild cannot be run in the 'production' environment.`, so this test breaks and no task owns it. Both assertions sit in one test case, so splitting them across 003 and 005 (which could run in parallel) would conflict. Fix: task 005 owns the file, updates both assertions to the new guard text, and now depends on 003. Any new integration case that passes `--force` in a non-dev environment must also pass `--no-interaction`, because `StdinConfirmationPrompter::isInteractive()` is true when the suite runs from a terminal and the run would hang waiting on stdin.

## Important (Should fix before building)

2. **`$effect` strings are undefined (tasks 002, 003, 005, 006).** Task 002 puts `<effect>` into two sentences ("This command <effect> and is never allowed in production" / "This command <effect> in the 'staging' environment. Continue?"), but no task says what each command passes. Parallel workers will invent different wording, and docs (006) can't quote it. Fix: pin per-command effects that read correctly in both sentences.
3. **Existing unit tests construct commands with `appEnvironment:` and assert old strings (tasks 003, 005).** `RollbackCommandTest` builds the command 13 times with `appEnvironment:`, `RebuildCommandTest`/`ResetCommandTest` do the same, and `SeedCommandTest::createSeedCommand()` passes `appEnvironment:` to `SeedCommand`. All of them still assert the old production messages. Fix: state explicitly that the helpers and constructions switch to `destructiveCommandGuard: new DestructiveCommandGuard(new AppEnvironment([...]), new FakeConfirmationPrompter(...))`, and that the old-message assertions move to the new text. Keep the `SeederRunner` in `createSeedCommand()` on the same `AppEnvironment`.
4. **Guard ordering in `SeedCommand` (task 005).** The guard must run before seeder discovery, as the current production check does, and `--force` must reach both `runAll()` and `runByName()` (`--class`). Otherwise a staging `--force --class users` run gets past the guard and then throws `requiresForce` from the runner. Fix: add requirements.
5. **Docs miss stale statements (task 006).** `packages/docs-markdown/docs/guides/database.md:97` says the four commands "refuse to run in production". `core.md:561-562` (API reference) lists only `isProduction()`/`isDevelopment()`. `database.md:1389-1392` (the CLI table) says "(refused in production)". Fix: add them to task 006.
6. **`SeederException::blockedInProduction()` suggestion is outdated (task 004).** It says "Set MARKO_ENV or APP_ENV to development/local". Testing is now allowed too. Fix: mention `testing`. `requiresForce()` should name the environment and say `--force` / `force: true`.

## Minor (Nice to address)

- `TestDatabase::assertDisposable()` (marko/testing) refuses development but allows staging. That is the opposite of the new command policy. It is a deliberate, separate guard, but docs may want to note the difference.
- `isTesting()` is not yet used by `errors-simple`/`errors-advanced`. Fine, just don't widen scope.
- Guard tests in 002 and command tests in 003/005 overlap heavily. The command tests could limit themselves to wiring (one refuse, one allow, migrator not called), but this is acceptable as written.

## Questions for the Team

- **`--force` semantics differ from `db:migrate`.** In `MigrateCommand::confirmDestructiveChanges()`, `--force` skips the prompt. In this plan, `--force` in staging still prompts when interactive (skipped only with `--no-interaction` or no TTY). The plan calls this "mirroring" migrate, but it isn't. Is the double opt-in intentional (issue #235 option C)? If yes, docs should say `--force --no-interaction` is the scripted form.
