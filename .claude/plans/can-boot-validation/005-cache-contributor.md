# Task 005: CanRouteCacheContributor (REMOVED)

**Status**: removed
**Depends on**: none
**Retry count**: 0

## Description
Removed by the devil's advocate review. `discovery:cache` always boots live (`CliKernel::LIVE_DISCOVERY_COMMANDS`), so the post-boot check (task 006) already fails it before `DiscoveryCompiler` runs. A contributor would run a second full route scan and store a `can_routes` section nothing reads, which is pseudo-functionality. Do not declare anything under `discovery` in authorization's module.php.

## Requirements (Test Descriptions)
None.
