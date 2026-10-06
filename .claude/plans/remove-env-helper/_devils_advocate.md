# Devil's Advocate Review: remove-env-helper

## Critical (Must fix before building)

1. **Task 001: `composer dump-autoload` does not drop the deleted file.** `marko/env` is a path repository. Composer rebuilds autoload from the snapshot in `vendor/composer/installed.json`, and that snapshot still lists `"files": ["src/functions.php"]` for `marko/env` (line ~5052). After `functions.php` is deleted, `dump-autoload` writes `vendor/composer/autoload_files.php` with `$vendorDir . '/marko/env/src/functions.php'` again, and every test run fails with "Failed opening required". Fix: run `composer update marko/env` (this refreshes the path package metadata), then check that `vendor/composer/autoload_files.php` no longer mentions `marko/env/src/functions.php`. `composer.lock` is gitignored and CI resolves fresh, so CI is not affected. The problem is local and for the orchestrator's test runs. Applied to task 001 and to Risks in `_plan.md`.

## Important (Should fix before building)

2. **Task 002: "Call to undefined function env()" is not guaranteed.** `env.md` currently has a paragraph about `function_exists` shadowing, which explains that `illuminate/support` can define its own global `env()`. After the removal, an app that has such a library installed gets no fatal error. Its leftover `env()` calls silently go to the other library's `env()`, which has different coercion. The upgrade note must say this, and it must tell readers to search their config for `env(` instead of waiting for a boot error. The current shadowing paragraph describes Marko's guard, which will no longer exist, so it should be replaced, not kept. Applied to task 002.

## Minor (Nice to address)

- Task 001's `function_exists('env')` test assumes that no installed dependency defines a global `env()`. That is true today: no `function env(` exists in `vendor/`. If a dev dependency adds one later (for example `illuminate/support`), the test fails for a reason unrelated to `marko/env`. That is acceptable as a loud signal.
- The worktree already contains partial implementation: `packages/env/composer.json` has no `files` entry, `EnvHelperRemovedTest.php` exists, and `env.md` contains a literal `@@NEW@@` placeholder at line 76. The worker must replace the placeholder. Task 002 now requires that no `@@NEW@@` remain.

## Questions for the Team

- Should the PR also add a short note to the 0.9.0 release notes or CHANGELOG by hand, or is the `breaking` label enough?
