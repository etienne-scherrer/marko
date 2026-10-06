# Devil's Advocate Review: lazy-admin-sections

## Critical (Must fix before building)
None.

## Important (Should fix before building)
1. **Sort-order regression (001).** The plan sorts definitions by the attribute `sortOrder`, but the attribute defaults to `0` and sections commonly set the real order in `getSortOrder()` (e.g. docs admin.md line 108 `#[AdminSection(id: 'catalog', label: 'Catalog')]`; ModuleWiringTest `ReportsSection` returns 30 with no attribute sortOrder). `all()` has to build every section anyway, so switching to the attribute value would silently reorder menus. Fix: `all()` builds everything, then sorts by the built instance's `getSortOrder()` (no change from today). A stable sort keeps registration order for ties.
2. **Missed call sites (003).** `new AdminSectionRegistry()` also appears in `packages/admin-api/tests/Unit/Controller/SectionControllerWiringTest.php:46` and `packages/admin-api/tests/Feature/AdminApiErrorShapeTest.php:93`. Neither is listed.
3. **Exception contract not defined (001).** The plan doesn't name the new factory. Fix: `AdminException::sectionBuildFailed(string $className, string $id, Throwable $previous)`, which chains `previous`. Wrap only the `$container->get()` call so the id-mismatch exception doesn't get wrapped. `sectionIdMismatch` and `sectionClassNotFound` have `context` text that says "at boot", which is now wrong for the mismatch case. Don't cache a failed build.
4. **Existing tests that now contradict the new behavior (002).** `ModuleWiringTest` "throws sectionIdMismatch when getId differs from the attribute id" expects a throw at boot. It has to be rewritten, not just added to.

## Minor (Nice to address)
- If one section's build fails, `all()` fails too, so every admin page (menu) errors. That's loud, which is acceptable, but the docs should say so.
- Adding `registerDefinition()` to the interface breaks third-party implementers. Call this out in the release notes.

## Questions for the Team
- Should `all()` skip a failing section and keep going, or fail the whole call? The plan assumes it fails.
