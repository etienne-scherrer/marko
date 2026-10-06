<?php

declare(strict_types=1);

/*
 * Benchmark: per-request boot cost with and without the discovery cache.
 *
 * Generates a throwaway project (vendor/marko/* linked to this monorepo, a
 * set of non-Marko vendor packages, and N app modules holding controllers
 * and plain classes), then times Application::boot() plus a 404 dispatch in
 * a fresh PHP process per request, the way PHP-FPM serves each request. The
 * opcache file cache is warmed first so compiled scripts are reused across
 * processes, as a warm FPM opcache would.
 *
 * Usage:
 *   php bin/benchmark-discovery-cache.php [--modules=50] [--classes=600]
 *       [--vendor-packages=90] [--runs=15] [--min-ratio=3] [--keep]
 *
 * Exits 1 when the cached boot is less than --min-ratio times faster.
 */

const BENCH_MARKO_PACKAGES = ['core', 'routing', 'env'];

/**
 * @return array{modules: int, classes: int, vendorPackages: int, runs: int, minRatio: float, keep: bool}
 */
function benchOptions(): array
{
    $options = getopt('', ['modules:', 'classes:', 'vendor-packages:', 'runs:', 'min-ratio:', 'keep']);

    return [
        'modules' => max(1, (int) ($options['modules'] ?? 50)),
        'classes' => max(1, (int) ($options['classes'] ?? 600)),
        'vendorPackages' => max(0, (int) ($options['vendor-packages'] ?? 90)),
        'runs' => max(1, (int) ($options['runs'] ?? 15)),
        'minRatio' => (float) ($options['min-ratio'] ?? 3),
        'keep' => array_key_exists('keep', $options),
    ];
}

function benchWrite(string $path, string $content): void
{
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true) && !is_dir(dirname($path))) {
        throw new RuntimeException('Could not create ' . dirname($path));
    }

    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Could not write $path");
    }
}

/**
 * @param array{modules: int, classes: int, vendorPackages: int, runs: int, minRatio: float, keep: bool} $options
 */
function benchBuildProject(string $root, string $project, array $options): void
{
    foreach (BENCH_MARKO_PACKAGES as $package) {
        benchWrite("$project/vendor/marko/.keep", '');
        symlink("$root/packages/$package", "$project/vendor/marko/$package");
    }

    benchWrite(
        "$project/vendor/autoload.php",
        "<?php\n\nreturn require " . var_export("$root/vendor/autoload.php", true) . ";\n",
    );

    // A real installed.json, so the stale check hashes a realistically sized file.
    $installed = "$root/vendor/composer/installed.json";
    benchWrite("$project/vendor/composer/installed.json", is_file($installed) ? (string) file_get_contents($installed) : '{}');

    for ($i = 1; $i <= $options['vendorPackages']; $i++) {
        benchWrite("$project/vendor/thirdparty$i/package/composer.json", json_encode([
            'name' => "thirdparty$i/package",
            'require' => ['php' => '^8.5'],
            'autoload' => ['psr-4' => ["ThirdParty$i\\" => 'src/']],
        ], JSON_PRETTY_PRINT));
    }

    $perModule = max(3, intdiv($options['classes'], $options['modules']));

    for ($m = 1; $m <= $options['modules']; $m++) {
        $namespace = "Bench\\Module$m";
        $dir = "$project/app/module$m";

        benchWrite("$dir/composer.json", json_encode([
            'name' => "app/module$m",
            'autoload' => ['psr-4' => ["$namespace\\" => 'src/']],
            'extra' => ['marko' => ['module' => true]],
        ], JSON_PRETTY_PRINT));

        for ($c = 1; $c <= $perModule; $c++) {
            $isController = $c <= 2;
            $class = $isController ? "Controller$c" : "Service$c";
            $body = $isController
                ? <<<PHP
                    #[Get('/module$m/c$c')]
                    public function index(): Response { return new Response('index'); }

                    #[Get('/module$m/c$c/{id:\\d+}', name: 'module$m.c$c.show')]
                    public function show(int \$id): Response { return new Response("show \$id"); }

                    #[Post('/module$m/c$c')]
                    public function store(): Response { return new Response('stored', 201); }
                PHP
                : "    public function handle(int \$value): int { return \$value * $c; }\n";

            benchWrite("$dir/src/$class.php", <<<PHP
                <?php

                declare(strict_types=1);

                namespace $namespace;

                use Marko\\Routing\\Attributes\\Get;
                use Marko\\Routing\\Attributes\\Post;
                use Marko\\Routing\\Http\\Response;

                class $class
                {
                $body
                }
                PHP);
        }
    }

    benchWrite("$project/bench-request.php", <<<'PHP'
        <?php

        declare(strict_types=1);

        require __DIR__ . '/vendor/autoload.php';

        $start = hrtime(true);
        $app = Marko\Core\Application::boot(__DIR__);
        $response = $app->router->handle(new Marko\Routing\Http\Request(server: [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/does-not-exist',
        ]));
        $elapsed = (hrtime(true) - $start) / 1e6;

        if ($response->statusCode() !== 404) {
            fwrite(STDERR, "Expected a 404, got {$response->statusCode()}\n");
            exit(1);
        }

        echo json_encode(['ms' => $elapsed, 'memory' => memory_get_peak_usage()]);
        PHP);

    benchWrite("$project/bench-compile.php", <<<'PHP'
        <?php

        declare(strict_types=1);

        require __DIR__ . '/vendor/autoload.php';

        $app = new Marko\Core\Application(__DIR__ . '/vendor', __DIR__ . '/modules', __DIR__ . '/app');
        $app->initialize(false);

        exit($app->commandRunner->run(
            'discovery:cache',
            new Marko\Core\Command\Input(['marko', 'discovery:cache']),
            new Marko\Core\Command\Output(fopen('php://stdout', 'w')),
        ));
        PHP);
}

/**
 * @param array<string, string> $env
 * @return array{ms: float, memory: int}
 */
function benchRequest(string $project, string $opcacheDir, array $env): array
{
    $ini = function_exists('opcache_get_status')
        ? sprintf(
            // file_update_protection=0: the generated files are seconds old, and opcache
            // otherwise refuses to cache files modified in the last 2 seconds.
            '-d opcache.enable_cli=1 -d opcache.file_cache=%s -d opcache.file_cache_only=1'
                . ' -d opcache.file_update_protection=0 -d opcache.jit=off',
            escapeshellarg($opcacheDir),
        )
        : '';
    $prefix = implode(' ', array_map(
        fn (string $key, string $value): string => "$key=" . escapeshellarg($value),
        array_keys($env),
        $env,
    ));

    $command = "$prefix " . escapeshellarg(PHP_BINARY) . " $ini " . escapeshellarg("$project/bench-request.php") . ' 2>&1';
    exec($command, $output, $exitCode);
    $result = json_decode(implode("\n", $output), true);

    if ($exitCode !== 0 || !is_array($result)) {
        throw new RuntimeException("Benchmark request failed:\n" . implode("\n", $output));
    }

    return ['ms' => (float) $result['ms'], 'memory' => (int) $result['memory']];
}

/**
 * @param array<int, float> $values
 */
function benchMedian(array $values): float
{
    sort($values);
    $count = count($values);
    $middle = intdiv($count, 2);

    return $count % 2 === 1 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
}

/**
 * @param array<string, string> $env
 * @return array{median: float, min: float, memory: int}
 */
function benchMeasure(string $project, string $opcacheDir, array $env, int $runs): array
{
    // Warm the opcache file cache.
    benchRequest($project, $opcacheDir, $env);
    benchRequest($project, $opcacheDir, $env);

    $times = [];
    $memory = 0;

    for ($run = 0; $run < $runs; $run++) {
        $result = benchRequest($project, $opcacheDir, $env);
        $times[] = $result['ms'];
        $memory = max($memory, $result['memory']);
    }

    return ['median' => benchMedian($times), 'min' => min($times), 'memory' => $memory];
}

function benchRemove(string $path): void
{
    if (is_link($path) || is_file($path)) {
        unlink($path);

        return;
    }

    if (!is_dir($path)) {
        return;
    }

    foreach (scandir($path) ?: [] as $item) {
        if ($item !== '.' && $item !== '..') {
            benchRemove("$path/$item");
        }
    }

    rmdir($path);
}

$options = benchOptions();
$root = dirname(__DIR__);
$project = sys_get_temp_dir() . '/marko-benchmark-' . bin2hex(random_bytes(6));
$opcacheDir = "$project/.opcache";

try {
    benchBuildProject($root, $project, $options);
    mkdir($opcacheDir, 0755, true);

    if (!function_exists('opcache_get_status')) {
        fwrite(STDERR, "Warning: opcache is not available; timings include script compilation.\n");
    }

    $env = ['APP_ENV' => 'production', 'DISCOVERY_CACHE_PATH' => "$project/storage/cache/discovery.php"];
    $uncached = benchMeasure($project, $opcacheDir, $env + ['DISCOVERY_CACHE_ENABLED' => '0'], $options['runs']);

    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg("$project/bench-compile.php") . ' 2>&1', $compileOutput, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException("discovery:cache failed:\n" . implode("\n", $compileOutput));
    }

    $cached = benchMeasure($project, $opcacheDir, $env + ['DISCOVERY_CACHE_ENABLED' => '1'], $options['runs']);
    $ratio = $uncached['median'] / $cached['median'];

    printf(
        "Fixture: %d app modules, %d classes, %d non-Marko vendor packages; %d runs each, opcache %s\n\n",
        $options['modules'],
        max(3, intdiv($options['classes'], $options['modules'])) * $options['modules'],
        $options['vendorPackages'],
        $options['runs'],
        function_exists('opcache_get_status') ? 'file cache (warm)' : 'unavailable',
    );
    printf("%-10s %12s %12s %12s\n", 'Boot', 'median ms', 'min ms', 'peak MB');
    printf("%-10s %12.2f %12.2f %12.1f\n", 'uncached', $uncached['median'], $uncached['min'], $uncached['memory'] / 1048576);
    printf("%-10s %12.2f %12.2f %12.1f\n", 'cached', $cached['median'], $cached['min'], $cached['memory'] / 1048576);
    printf("\nSpeed-up: %.1fx (minimum %.1fx)\n", $ratio, $options['minRatio']);

    if ($ratio < $options['minRatio']) {
        fwrite(STDERR, sprintf("FAIL: cached boot is only %.1fx faster; expected at least %.1fx\n", $ratio, $options['minRatio']));
        exit(1);
    }
} finally {
    if ($options['keep']) {
        echo "Project kept at $project\n";
    } else {
        benchRemove($project);
    }
}
