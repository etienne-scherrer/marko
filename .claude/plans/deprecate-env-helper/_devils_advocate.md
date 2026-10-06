# Devil's Advocate Review: deprecate-env-helper

## Critical (Must fix before building)
None.

## Important (Should fix before building)

1. **Task 001: `envDeprecationReplacement()` adds a second global function.** The Implementation Notes add a new global helper next to `env()`. That breaks the "no global helpers" rule and adds one more name that can collide with other libraries, all for a single `match`. Fix: put the replacement lookup inline in `env()` (`match (get_debug_type($default)) { 'bool' => 'bool', 'int' => 'int', 'float' => 'float', 'string' => 'string', 'array' => 'list', default => 'nullableString' }`). Add no new global symbol.

2. **Task 001: the deprecation must fire before the early `return $default`.** In `functions.php`, an unset variable returns at line 32, before any coercion. If the notice is triggered after the lookup, calls for unset variables, which are the common case, never warn. Fix: trigger as the first statement. Added the requirement "it emits even when the variable is unset and the default is returned".

3. **Task 001: test error-handler hygiene.** Pest 4 / PHPUnit 12 mark a test risky when it leaves its own error handler installed. The scoped handler must:
   - capture only `E_USER_DEPRECATED` and return `false` for every other level;
   - be removed with `restore_error_handler()` in `afterEach`.

   The existing 13 tests in `EnvFunctionTest.php` call `env()` repeatedly (up to 4 times per test), so the handler has to cover the whole file, not only the new tests. Requirements were added for this.

4. **Task 001 to Task 003 contract: the message format is not pinned down.** Task 003 documents the notice and task 001 tests it, so both need the exact wording. Fix: added a canonical format to `_plan.md` Architecture Notes and to task 001.

5. **Task 002: the worktree is already partly done.** In the current worktree, none of the six `composer.json` files lists `marko/env`, and `.claude/pr-review-process.md:138` already has the new rule. Still open:
   - `docs/packages/inertia.md:14` ("depends on ... and `marko/env`");
   - the Related Packages lines in `vite.md`, `inertia.md`, `inertia-react.md`, `inertia-vue.md` and `inertia-svelte.md`.

   `composer.lock` still records `"marko/env": "self.version"` under these path packages (e.g. `marko/vite` at composer.lock:6231). Fix: the task now says to verify existing edits instead of redoing them, and to refresh the lock entries for the six packages (`composer update marko/debugbar marko/inertia marko/inertia-react marko/inertia-vue marko/inertia-svelte marko/vite --lock`) so the lock matches the manifests.

## Minor (Nice to address)
- `trigger_error()` reports `packages/env/src/functions.php` as the file/line, not the caller's config file. The variable name in the message makes up for this, but the docs could say to search for `env('KEY'`.
- The suggestion `Env::nullableString` for a call with no default does not keep `env()`'s `'true'` → `true` coercion. Task 003's coercion mapping table should say that a no-default `env('APP_DEBUG')` that relied on coercion should become `Env::bool('APP_DEBUG', false)`.
- `packages/env/README.md:3` and `env.md` front-matter `description` both advertise "provides the env() helper". Task 003 covers both, but the front matter is easy to miss.

## Questions for the Team
- Should the Related Packages entries for `marko/env` on the vite/inertia* docs pages be removed, or kept and reworded (for example "loads `.env`; required by the app, not by this package")? Their current text is still accurate as "related". Only the dependency sentence on `inertia.md:14` is factually wrong.
- Should the follow-up issue for removal in 1.0 be opened as part of this PR, and its number put in the `@deprecated` docblock?
