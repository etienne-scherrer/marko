<?php

declare(strict_types=1);

/**
 * Shipped config files read environment variables only through Marko\Config\Env (#289).
 *
 * Casts, filter_var(), the global env() helper and raw $_ENV/getenv() reads turn a
 * misspelled value into 0, false or true without an error. This scans the tokens of
 * every packages/{package}/config/*.php file, so comments and strings are ignored.
 * It lives in the monorepo suite because each package is split into its own repository.
 */

/**
 * @return list<array{file: string, line: int, pattern: string}>
 */
function findForbiddenConfigEnvReads(
    string $file,
): array {
    $tokens = array_values(array_filter(
        token_get_all((string) file_get_contents($file)),
        fn (array|string $token): bool => !is_array($token)
            || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true),
    ));
    $casts = [T_INT_CAST => '(int)', T_DOUBLE_CAST => '(float)', T_BOOL_CAST => '(bool)'];
    $functions = ['env', 'getenv', 'filter_var'];
    $violations = [];

    foreach ($tokens as $index => $token) {
        if (!is_array($token)) {
            continue;
        }

        [$id, $text, $line] = $token;
        $previous = $tokens[$index - 1] ?? null;
        $next = $tokens[$index + 1] ?? null;
        $pattern = null;

        if (isset($casts[$id])) {
            $pattern = $casts[$id];
        } elseif ($id === T_VARIABLE && $text === '$_ENV') {
            $pattern = '$_ENV';
        } elseif (
            in_array($id, [T_STRING, T_NAME_FULLY_QUALIFIED], true)
            && in_array(strtolower(ltrim($text, '\\')), $functions, true)
            && $next === '('
            && !(is_array($previous) && in_array(
                $previous[0],
                [T_DOUBLE_COLON, T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_FUNCTION],
                true
            ))
        ) {
            $pattern = strtolower(ltrim($text, '\\')) . '()';
        }

        if ($pattern !== null) {
            $violations[] = ['file' => $file, 'line' => $line, 'pattern' => $pattern];
        }
    }

    return $violations;
}

/**
 * @return list<string>
 */
function shippedConfigFiles(): array
{
    $files = glob(dirname(__DIR__) . '/packages/*/config/*.php') ?: [];
    sort($files);

    return $files;
}

/**
 * @param list<array{file: string, line: int, pattern: string}> $violations
 * @return list<string>
 */
function describeConfigEnvViolations(
    array $violations,
): array {
    $root = dirname(__DIR__) . '/';

    return array_map(
        fn (array $violation): string => sprintf(
            '%s:%d uses %s',
            str_replace($root, '', $violation['file']),
            $violation['line'],
            $violation['pattern'],
        ),
        $violations,
    );
}

/**
 * @param list<string> $patterns
 * @return list<string>
 */
function shippedConfigViolationsMatching(
    array $patterns,
): array {
    $violations = array_merge(...array_map(findForbiddenConfigEnvReads(...), shippedConfigFiles()));

    return describeConfigEnvViolations(array_values(array_filter(
        $violations,
        fn (array $violation): bool => in_array($violation['pattern'], $patterns, true),
    )));
}

it('scans the shipped config files', function (): void {
    expect(count(shippedConfigFiles()))->toBeGreaterThan(40);
});

it('finds no casts or filter_var in shipped config files', function (): void {
    expect(shippedConfigViolationsMatching(['(int)', '(float)', '(bool)', 'filter_var()']))->toBe([]);
});

it('finds no global env() calls in shipped config files', function (): void {
    expect(shippedConfigViolationsMatching(['env()']))->toBe([]);
});

it('finds no raw $_ENV or getenv reads in shipped config files', function (): void {
    expect(shippedConfigViolationsMatching(['$_ENV', 'getenv()']))->toBe([]);
});

it('flags each banned pattern in a violating fixture, ignoring comments and strings', function (): void {
    $violations = findForbiddenConfigEnvReads(__DIR__ . '/Fixtures/ConfigEnvReads/violating.php');

    expect(array_map(
        fn (array $violation): string => $violation['line'] . ' ' . $violation['pattern'],
        $violations,
    ))->toBe([
        '9 (int)',
        '9 $_ENV',
        '10 (float)',
        '10 getenv()',
        '11 filter_var()',
        '12 env()',
        '13 (bool)',
    ]);
});

it('does not mistake Env::bool() or a method named env for the global helper', function (): void {
    $file = tempnam(sys_get_temp_dir(), 'config-env-reads');
    file_put_contents(
        $file,
        "<?php\nreturn ['a' => Marko\\Config\\Env::bool('A', true), 'b' => \$object->env('B')];\n"
    );

    try {
        expect(findForbiddenConfigEnvReads($file))->toBe([]);
    } finally {
        unlink($file);
    }
});
