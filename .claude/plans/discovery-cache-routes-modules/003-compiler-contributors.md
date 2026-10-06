# Task 003: DiscoveryCompiler runs contributors; discovery:cache output

**Status**: pending
**Depends on**: 001, 002
**Retry count**: 0

## Description
`DiscoveryCompiler` takes the container, includes the module list and the resolved global middleware in the payload, and runs every contributor declared by the modules (resolved from the container), storing each result under its key. Invalid contributors, duplicate keys and non-var_export-able data fail loudly. `discovery:cache` prints counts for modules, global middleware and each section.

## Context
- Payload keys are fixed by task 002 (`modules` as `CachedModule::fromManifest()` list, `globalMiddleware` from `GlobalMiddlewareResolver`, `sections`).
- Constructor gains `ContainerInterface`; `DiscoveryCacheCommand` autowires it. Update existing `DiscoveryCompilerTest` (`new DiscoveryCompiler()`) and `DiscoveryCacheCommandTest`.
- Export check is recursive: only scalars, null and arrays (no objects, closures, resources).
- Contributor class must exist (`class_exists`) before resolving; missing class is a loud error naming the declaring module.

## Requirements (Test Descriptions)
- [ ] `it stores each contributor result under its key`
- [ ] `it resolves contributors through the container`
- [ ] `it throws when a declared contributor does not implement DiscoveryCacheContributorInterface`
- [ ] `it throws when two contributors use the same key`
- [ ] `it throws when a contributor returns data that cannot be exported`
- [ ] `it includes the module list and global middleware order in the payload`
- [ ] `it reports module, middleware and section counts from discovery:cache`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
