<?php

declare(strict_types=1);

/**
 * Run bin/benchmark-discovery-cache.php on a tiny fixture.
 *
 * @return array{exitCode: int, output: string}
 */
function runDiscoveryCacheBenchmark(string $minRatio): array
{
    $script = dirname(__DIR__) . '/bin/benchmark-discovery-cache.php';
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script)
        . " --modules=2 --classes=6 --vendor-packages=2 --runs=1 --min-ratio=$minRatio 2>&1";

    exec($command, $output, $exitCode);

    return ['exitCode' => $exitCode, 'output' => implode("\n", $output)];
}

it('prints uncached and cached medians and the ratio', function (): void {
    $result = runDiscoveryCacheBenchmark('0');

    expect($result['exitCode'])->toBe(0)
        ->and($result['output'])->toMatch('/uncached\s+\d+\.\d+/')
        ->and($result['output'])->toMatch('/cached\s+\d+\.\d+/')
        ->and($result['output'])->toMatch('/Speed-up: \d+\.\dx/');
});

it('fails when cached is less than 3x faster', function (): void {
    $result = runDiscoveryCacheBenchmark('1000');

    expect($result['exitCode'])->toBe(1)
        ->and($result['output'])->toContain('FAIL: cached boot is only');
});
