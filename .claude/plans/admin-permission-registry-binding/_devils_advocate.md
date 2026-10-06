# Devil's Advocate Review: admin-permission-registry-binding

## Critical (Must fix before building)

### C1. Task 004 docs would document a Preference that does not work (004)
`Container::resolve()` checks `PreferenceRegistry::getPreference($id)` only against the requested id (the interface). Once the interface binding maps to `PermissionRegistry::class`, the container instantiates that class directly. It does not check preferences again for the bound concrete class (packages/core/src/Container/Container.php lines 145-172). So `#[Preference(replaces: PermissionRegistry::class)]` is silently ignored for anything that injects `PermissionRegistryInterface`, which is every consumer. The docs must show `#[Preference(replaces: PermissionRegistryInterface::class)]`. Sharing is kept because `shared[]` is keyed by the original (interface) id. **Fix applied:** Task 004 now requires the interface-targeted Preference and warns against targeting the concrete class.

## Important (Should fix before building)

### I1. Wildcard test in 001 proves nothing about the singleton (001)
`PermissionRegistry::matches()` is stateless. It never reads `$this->permissions`. "A permission registered through an injected registry passes the middleware wildcard check" passes with or without the singleton, so it is not a regression guard. **Fix applied:** replaced it with a sharing test. Two separately autowired consumers of `PermissionRegistryInterface` are resolved, a permission is registered through one, and the test asserts it appears in the other's `all()`. Without `shared` this fails.

### I2. Middleware and SectionController have dependencies admin-auth's module.php does not bind (001, 002)
`AdminAuthMiddleware` needs `GuardInterface` and `AdminConfigInterface`. `SectionController` needs `AdminSectionRegistryInterface` and `GuardInterface`. None of these are bound by admin-auth's module.php. "Without an app-level binding" has to mean "without an app-level *registry* binding". The tests must still `instance()` the guard, admin config and section registry, or they fail with `BindingException`. **Fix applied:** made this explicit in 001 and 002.

### I3. Do not put the registry in both `bindings` and key-value `singletons` (001)
`BindingRegistry::registerModule()` calls `registerBinding()` for both arrays. Listing the same interface in both from the same module triggers `BindingConflictException::multipleBindings` (same priority). **Fix applied:** 001 now says singletons-only, and the test asserts the key is absent from `bindings`.

### I4. "Same instance into SectionController and AdminAuthMiddleware" has no observable seam (002)
Both hold the registry in a `private` (readonly) property, and the only method they call on it (`matches`) is stateless. **Fix applied:** 002 now specifies how to check this: read the private `permissionRegistry` property via `ReflectionProperty` on both objects and assert both are identical to `$container->get(PermissionRegistryInterface::class)`. 002 also specifies `BindingException` as the expected exception for the regression guard.

### I5. Task 003 must update existing expectations and cover the PHPStan contract (003)
No existing test asserts the silent behaviour, so nothing breaks there. But PHPStan must stay at zero errors: the method needs `@throws AdminException|ReflectionException`. Tests passing a non-existent class string need a `@phpstan-ignore` or a variable typed as `string`, because the param is `class-string`. **Fix applied:** noted in 003.

## Minor (Nice to address)

- `AdminSectionDiscovery::parseAdminSectionClass()` calls `$sectionAttributes[0]->newInstance()` without checking that the attribute exists. A class implementing `AdminSectionInterface` without `#[AdminSection]` raises a PHP `Error` (null method call), not an `AdminException`. Now that 003 makes failures loud, that path surfaces as an unhelpful Error. This is out of scope here and belongs in marko/admin.
- The 002 test will `require` admin-auth's module.php via a relative monorepo path (`__DIR__ . '/../../../../admin-auth/module.php'`). Resolving it from `ReflectionClass(PermissionRegistry::class)` (`dirname(..., 2) . '/module.php'`) is more robust if packages are ever tested from vendor.
- The router test (001) can keep its `instance()` calls for guard and config. Only the registry line should move to module bindings.

## Questions for the Team

- Should `PermissionDiscovery` be wired into boot (e.g., discovering from `AdminSectionRegistry`)? Without a caller, the now-loud errors never fire in production. The plan correctly leaves this out of scope, but it deserves a follow-up issue.
