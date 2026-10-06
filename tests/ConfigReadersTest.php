<?php

declare(strict_types=1);

/**
 * Every shipped config key has a reader (#403).
 *
 * A config key that nothing reads looks like a control an operator can turn
 * (admin-api.rate_limit, admin-auth.super_admin_role) while doing nothing, which
 * is pseudo-functionality. This guards both halves of that:
 *
 * - every top-level key of packages/{package}/config/{name}.php appears as a
 *   '{name}.{key}' string literal in that package's src/ or module.php, or in any
 *   package's when the file merges into another package's config (inertia-react's
 *   inertia.php, admin-auth's authentication.php);
 * - every public method of a *Config class in packages/{package}/src that reads
 *   ConfigRepositoryInterface is called somewhere in packages/{package}/src or
 *   module.php of some package, so a key read only by an unused accessor counts as
 *   dead too.
 *
 * Intentional exceptions, each with a reason, live in config-readers-allowlist.php.
 * It is a text search, not a call graph, so it can miss a dead key whose accessor
 * shares a name with a live one; it cannot report a live key as dead without the
 * allowlist saying why. It lives in the monorepo suite because each package is
 * split into its own repository.
 */

/**
 * @return array{keys: array<string, string>, accessors: array<string, string>}
 */
function configReadersAllowlist(): array
{
    return require __DIR__ . '/config-readers-allowlist.php';
}

/**
 * The PHP source of a package's src/ directory and module.php, concatenated.
 */
function configReadersPackageSource(
    string $packageDir,
): string {
    $source = is_file("$packageDir/module.php") ? (string) file_get_contents("$packageDir/module.php") : '';

    if (!is_dir("$packageDir/src")) {
        return $source;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator("$packageDir/src", FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
            $source .= (string) file_get_contents($file->getPathname());
        }
    }

    return $source;
}

/**
 * @return array<string, string> package directory => source
 */
function configReadersAllSources(): array
{
    static $sources = null;

    if ($sources === null) {
        $sources = [];

        foreach (glob(dirname(__DIR__) . '/packages/*', GLOB_ONLYDIR) ?: [] as $packageDir) {
            $sources[$packageDir] = configReadersPackageSource($packageDir);
        }
    }

    return $sources;
}

function configReadersSourceReadsKey(
    string $source,
    string $key,
): bool {
    return str_contains($source, "'$key") || str_contains($source, "\"$key");
}

/**
 * Shipped config keys ('{name}.{key}') with no string-literal reader.
 *
 * @return list<string>
 */
function configReadersUnreadKeys(): array
{
    $sources = configReadersAllSources();
    $everything = implode("\n", $sources);
    $unread = [];

    foreach (glob(dirname(__DIR__) . '/packages/*/config/*.php') ?: [] as $configFile) {
        $packageDir = dirname($configFile, 2);
        $name = basename($configFile, '.php');
        $config = require $configFile;

        foreach (array_keys(is_array($config) ? $config : []) as $topLevelKey) {
            $key = "$name.$topLevelKey";

            if (
                !configReadersSourceReadsKey($sources[$packageDir], $key)
                && !configReadersSourceReadsKey($everything, $key)
            ) {
                $unread[] = $key;
            }
        }
    }

    sort($unread);

    return array_values(array_unique($unread));
}

/**
 * Public methods of *Config classes that read ConfigRepositoryInterface and that
 * nothing in packages/ calls, as 'Class::method'.
 *
 * @return list<string>
 */
function configReadersUncalledAccessors(): array
{
    $everything = implode("\n", configReadersAllSources());
    $uncalled = [];

    foreach (array_keys(configReadersAllSources()) as $packageDir) {
        if (!is_dir("$packageDir/src")) {
            continue;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator("$packageDir/src", FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (!$file instanceof SplFileInfo || !str_ends_with($file->getFilename(), 'Config.php')) {
                continue;
            }

            $code = (string) file_get_contents($file->getPathname());

            if (
                !str_contains($code, 'ConfigRepositoryInterface')
                || preg_match('/^namespace ([^;]+);/m', $code, $namespace) !== 1
                || preg_match('/^(?:readonly |abstract )*class (\w+)/m', $code, $class) !== 1
            ) {
                continue;
            }

            preg_match_all('/public function (\w+)\(/', $code, $methods);

            foreach ($methods[1] as $method) {
                if ($method !== '__construct' && preg_match('/(->|::)' . $method . '\(/', $everything) !== 1) {
                    $uncalled[] = "$namespace[1]\\$class[1]::$method";
                }
            }
        }
    }

    sort($uncalled);

    return $uncalled;
}

it('scans the shipped config files and config accessors', function (): void {
    expect(count(glob(dirname(__DIR__) . '/packages/*/config/*.php') ?: []))->toBeGreaterThan(40)
        ->and(configReadersAllSources())->not->toBeEmpty();
});

it('finds a reader for every shipped config key', function (): void {
    $unread = array_values(array_diff(configReadersUnreadKeys(), array_keys(configReadersAllowlist()['keys'])));

    expect($unread)->toBe([]);
});

it('finds a caller for every public config accessor', function (): void {
    $uncalled = array_values(array_diff(
        configReadersUncalledAccessors(),
        array_keys(configReadersAllowlist()['accessors']),
    ));

    expect($uncalled)->toBe([]);
});

it('keeps only allowlist entries that are still needed, each with a reason', function (): void {
    $allowlist = configReadersAllowlist();

    expect(array_values(array_diff(array_keys($allowlist['keys']), configReadersUnreadKeys())))->toBe([])
        ->and(array_values(array_diff(array_keys($allowlist['accessors']), configReadersUncalledAccessors())))->toBe([])
        ->and(array_filter([...$allowlist['keys'], ...$allowlist['accessors']], fn (string $reason): bool => trim($reason) === ''))
            ->toBe([]);
});

it('reports the security-looking keys from #403 as unread until they are wired or removed', function (): void {
    $source = "<?php\n\$this->config->getString('admin-auth.guard');\n";

    expect(configReadersSourceReadsKey($source, 'admin-auth.guard'))->toBeTrue()
        ->and(configReadersSourceReadsKey($source, 'admin-auth.super_admin_role'))->toBeFalse()
        ->and(configReadersSourceReadsKey($source, 'admin-api.rate_limit'))->toBeFalse();
});
