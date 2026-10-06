# Task 002: Drop stale marko/env requires and their docs mentions

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
debugbar, inertia, inertia-react, inertia-vue, inertia-svelte and vite require `marko/env` only for the `env()` calls removed in #334. Remove the require and the docs lines that list `marko/env` as a dependency; apps get `.env` loading from the skeleton's own `marko/env` require.

## Context
- Related files: `packages/{debugbar,inertia,inertia-react,inertia-vue,inertia-svelte,vite}/composer.json`, `packages/docs-markdown/docs/packages/{vite,inertia,inertia-react,inertia-vue,inertia-svelte}.md`, `.claude/pr-review-process.md`

## Requirements (Test Descriptions)
- [x] none of the six composer.json files require `marko/env`
- [x] none of the six packages reference `env()` or `Marko\Env` in PHP
- [x] docs pages no longer list `marko/env` as a dependency
- [x] PR-review rule points configs at `marko/config` instead of `marko/env`

- [x] `composer.lock` entries for the six path packages no longer list `marko/env` in `require` (N/A: `composer.lock` is gitignored in this monorepo)
- [x] `inertia.md:14` dependency sentence no longer names `marko/env`

## Acceptance Criteria
- `composer validate` passes for each edited composer.json
- Package tests still pass

## Implementation Notes
Pure metadata/docs change; verified by grep and the existing package test suites.

The worktree is already partly done: the six `composer.json` files no longer list `marko/env`, and `.claude/pr-review-process.md:138` already has the new rule. Verify these instead of redoing them.

Remaining work:
- `packages/docs-markdown/docs/packages/inertia.md:14` (the "depends on ... and `marko/env`" sentence).
- The Related Packages `marko/env` lines in `vite.md`, `inertia.md`, `inertia-react.md`, `inertia-vue.md` and `inertia-svelte.md`. Reword them so it is clear the app, not this package, requires `marko/env`.
- `composer.lock` still records `"marko/env": "self.version"` under these path packages (e.g. `marko/vite`). Refresh it with `composer update marko/debugbar marko/inertia marko/inertia-react marko/inertia-vue marko/inertia-svelte marko/vite --lock`. Check that the diff only touches those packages' entries.
