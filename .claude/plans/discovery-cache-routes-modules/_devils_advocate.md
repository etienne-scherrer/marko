# Devil's Advocate Review: discovery-cache-routes-modules

## Critical (Must fix before building)

1. **ModuleAutoloader re-scans on every boot (004).** `Application::registerAutoloaders()` calls `ModuleAutoloader::register()`, which builds its own `ModuleDiscovery(new ManifestParser())` and runs `discoverInModules()` + `discoverInApp()` (json_decode + `ManifestParser::parse`). The task says "register PSR-4 autoloaders from the cache" but does not say how. Unless that is spelled out, the success criterion "zero ManifestParser::parse calls" fails. Fix: add `ModuleAutoloader::registerModules(array $modules)` (the same idempotent PSR-4 logic, no discovery), and have `register()` delegate to it. On a cached boot, call it with the cached non-vendor modules. Leave the live path and TestCase unchanged.
2. **Who passes CachedDiscovery and ClassFileParser to RoutingBootstrapper (004/005)?** `Application::discoverRoutes()` builds the bootstrapper with `new ClassFileParser()` and calls `GlobalMiddlewareResolver` directly. Tasks 005 and 006 both depend only on 004, so they run in parallel, and nothing says which one edits Application.php. Fix: 004 owns all Application.php changes. It passes the injected ClassFileParser and uses the cached global middleware on a cached boot. 005 reads `CachedDiscovery` from the container inside `RoutingBootstrapper::boot()` and does not touch Application.php.
3. **CachedDiscovery contract undefined (004 -> 005, 006).** Workers need the exact class, constructor and methods. The database boot closure also runs through `container->call()` in tests that never boot Application, so the type has to autowire. Fix: define `Marko\Core\Discovery\CachedDiscovery`, `__construct(?array $sections = null)` (a no-arg instance is uncached), `isCached(): bool`, and `section(string $key): ?array`. `section()` throws a new `DiscoveryCacheException::missingSection($key)` when the boot is cached but the key is absent.

## Important (Should fix before building)

4. **Task 001 is partly implemented already.** `DiscoveryCacheContributorInterface.php`, `Module/CachedModule.php` and `tests/Unit/Module/ManifestParserCachedTest.php` already exist in the worktree. The worker must build on these files and not recreate them.
5. **Paths and fingerprint inputs (002).** Unit tests build `new Application(vendorPath: $tmp/vendor, modulesPath: '', appPath: '')`, and module paths are not always under the base path. Fix: both write and load use `ProjectPaths` (base, vendor, modules, app) for the fingerprint and for relativizing. A path outside base is stored absolute, never as `../`.
6. **Edits to module.php and app/modules composer.json go undetected (002/004).** The cached global middleware and module order can silently diverge from a live module.php (changed `sequence` or `globalMiddleware`). A changed autoload or require in an app/modules composer.json also goes unnoticed. Fix: CachedModule snapshots `after`, `before` and `globalMiddleware`, and the cached boot throws `stale` when the live module.php differs. This costs little because module.php is required anyway. The fingerprint also hashes the contents of the composer.json files it finds under app/ and modules/ (no json_decode).
7. **Payload shape shared by 002 and 003 is unspecified.** Fix: define it in 002 as `modules: list<CachedModule>`, `globalMiddleware: list<class-string>`, `sections: array<string, array>`, alongside the existing four keys.
8. **CliKernel parses the command name after `initialize()` (004).** The Input has to be parsed first. Call `initialize(false)` positionally: CliKernelTest injects fake apps through the factory closure, and a named argument would throw "Unknown named parameter" on a fake.
9. **Existing tests will break (003/004).** `DiscoveryCompilerTest` uses `new DiscoveryCompiler()`, and `ApplicationDiscoveryCacheTest` writes v2-shaped payloads. Both tasks should list the update explicitly.
10. **The benchmark is unrunnable and too slow as written (007).** The generated project needs a vendor dir. Reuse the integration Helpers approach: symlink `vendor/marko/*` to `packages/*` and point `vendor/autoload.php` at the root autoloader. Opcache may be missing in CI. The tests also cannot generate 600 classes and spawn processes inside `composer test`. Fix: the script takes options for modules, classes, runs and min-ratio. It warns and continues when opcache is unavailable. Tests use a tiny fixture with `--min-ratio=0` and `--min-ratio=1000`.
11. **The "controllers not loaded" test needs isolation (005).** Each test must generate uniquely named controller classes and assert `class_exists($c, false) === false`. Otherwise classes loaded by earlier tests in the same process mask the result.
12. **The integration test must keep the issue tag (008).** HarnessTest requires the flipped test to carry `->issue(173)`. It also needs `APP_ENV=production`, a temp `DISCOVERY_CACHE_PATH`, and env restored afterwards.

## Minor (Nice to address)

- `hash_file` of installed.json runs on every request. That is fine at its typical size (under 2 MB), but worth measuring in the benchmark.
- Path-repository (symlinked) vendor packages can change without touching installed.json.
- Fingerprint recursion stops at any composer.json, while ModuleDiscovery recurses into directories whose composer.json is not a Marko module. A module nested under such a directory and added later is not detected.
- A module that was disabled at cache time and is enabled later is not detected; the docs should say so.
- `bin/` is outside phpstan's `paths`, so the benchmark script is not statically analysed.

## Questions for the Team

- Should the live boot also reuse the resolved module list for autoloaders, removing the second modules/app scan? That would be a behaviour change, because disabled modules currently get autoloaders.
- Is a stale cache throwing on `marko list` (and on every command except discovery:cache/clear) the desired UX?
