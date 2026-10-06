# Devil's Advocate Review: admin-auth-stateless-guard

## Critical (Must fix before building)
None. Task 001 targets `AdminAuthMiddleware::handle()` (public, line 44), which only checks `wantsJson()` today. The `StatelessAdminGuard` fake (test line 71) and `captureHttpException()` (line 114) exist. Mirroring `AuthMiddleware`'s `instanceof StatelessGuardInterface` condition needs no new public API. `AdminAuthRouterTest` (feature, line 123) asserts the 302 on a stateful `FakeGuard`, so it keeps passing.

## Important (Should fix before building)
- **Task 002: the admin-api.md paragraph has to be rewritten, not just added to.** `admin-api.md:20` says "any other unauthenticated request is redirected to the admin login" and "Send `Accept: application/json` from API clients so they get the `401` rather than a redirect". After task 001 the first sentence is wrong for stateless guards. The advice is now only needed on a session guard. If a worker only appends a sentence, the page will contradict itself. Applied: the task 002 requirements now cover this.
- **Task 002: the tutorial makes the same overbroad claim.** `docs/tutorials/build-an-admin-panel.md:510` says "unauthenticated users are redirected to `/admin/login`". It is not in task 002's file list. Applied: the file and a requirement were added so it is qualified (browser request on the session guard; JSON or stateless guard gets a 401).

## Minor (Nice to address)
- Task 001: `_plan.md` Scope mentions a stateless `+json` variant, but the task has no requirement for it. It is covered implicitly by the existing JSON-stateless test, so a dedicated test is optional.
- Task 001: the `@throws` docblock is unchanged. That is fine, because `UnauthenticatedException` is already listed.

## Questions for the Team
- None blocking. The behaviour change for apps that pointed `AdminAuthMiddleware` at a stateless guard and relied on the redirect is intended by #303. It should be called out in the PR body.
