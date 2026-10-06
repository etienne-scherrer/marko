<?php

declare(strict_types=1);

use Marko\Tests\Support\DocsClassReference\DocsClassReferenceScanner;

require_once __DIR__ . '/Support/DocsClassReference/DocsClassReferenceScanner.php';

/*
 * Guards every `Marko\...` class reference in fenced PHP code blocks of the
 * package READMEs and the docs site against classes that do not exist.
 *
 * Fictional example modules should use a non-Marko namespace (`App\`, `Acme\`).
 * If one genuinely has to live under `Marko\`, add its namespace prefix to
 * $allowedFictionalPrefixes below rather than weakening the scan.
 */

$repositoryRoot = dirname(__DIR__);
$packagesRoot = $repositoryRoot . '/packages';
$fixturePackagesRoot = __DIR__ . '/Fixtures/DocsClassReference/packages';

/** @var list<string> $allowedFictionalPrefixes */
$allowedFictionalPrefixes = [];

/**
 * @param list<array{file: string, line: int, class: string}> $references
 * @return list<string>
 */
function formatDocsClassReferences(array $references, string $root): array
{
    return array_map(
        fn (array $reference): string => sprintf(
            '%s:%d references missing class %s',
            str_replace($root . '/', '', $reference['file']),
            $reference['line'],
            $reference['class'],
        ),
        $references,
    );
}

it('resolves every Marko class referenced in README and docs PHP snippets', function () use (
    $packagesRoot,
    $repositoryRoot,
    $allowedFictionalPrefixes,
): void {
    $files = DocsClassReferenceScanner::discoverMarkdownFiles($packagesRoot);
    $scanner = new DocsClassReferenceScanner($packagesRoot, $allowedFictionalPrefixes);

    $missing = formatDocsClassReferences($scanner->findUnresolved($files), $repositoryRoot);

    expect($files)->not->toBeEmpty()
        ->and($missing)->toBe([]);
});

it('discovers package READMEs and nested docs pages', function () use ($fixturePackagesRoot): void {
    $files = DocsClassReferenceScanner::discoverMarkdownFiles($fixturePackagesRoot);

    expect($files)->toBe([
        $fixturePackagesRoot . '/demo/README.md',
        $fixturePackagesRoot . '/docs-markdown/docs/nested/section/page.md',
    ]);
});

it('reports a bogus Marko class added to a README with its file and line', function () use (
    $packagesRoot,
    $fixturePackagesRoot,
): void {
    $readme = $fixturePackagesRoot . '/demo/README.md';
    $scanner = new DocsClassReferenceScanner($packagesRoot);

    expect($scanner->findUnresolved([$readme]))->toBe([
        ['file' => $readme, 'line' => 7, 'class' => 'Marko\Nope\Thing'],
    ]);
});

it('ignores Marko references outside fenced PHP code blocks', function () use (
    $packagesRoot,
    $fixturePackagesRoot,
): void {
    $scanner = new DocsClassReferenceScanner($packagesRoot);

    $classes = array_column($scanner->extractReferences($fixturePackagesRoot . '/demo/README.md'), 'class');

    expect($classes)->toBe(['Marko\Routing\Http\Response', 'Marko\Nope\Thing']);
});

it('handles indented fences, group uses, FQCNs, namespaces and function imports', function () use (
    $packagesRoot,
    $fixturePackagesRoot,
): void {
    $page = $fixturePackagesRoot . '/docs-markdown/docs/nested/section/page.md';
    $scanner = new DocsClassReferenceScanner($packagesRoot);

    expect($scanner->extractReferences($page))->toBe([
        ['file' => $page, 'line' => 9, 'class' => 'Marko\Routing\Attributes\Get'],
        ['file' => $page, 'line' => 9, 'class' => 'Marko\Routing\Attributes\Post'],
        ['file' => $page, 'line' => 10, 'class' => 'Marko\Fictional\Example\Service'],
        ['file' => $page, 'line' => 12, 'class' => 'Marko\Routing\Http\Response'],
        ['file' => $page, 'line' => 13, 'class' => 'Marko\Missing\Fqcn'],
    ])->and(array_column($scanner->findUnresolved([$page]), 'class'))->toBe([
        'Marko\Fictional\Example\Service',
        'Marko\Missing\Fqcn',
    ]);
});

it('skips references under an allowlisted fictional namespace prefix', function () use (
    $packagesRoot,
    $fixturePackagesRoot,
): void {
    $page = $fixturePackagesRoot . '/docs-markdown/docs/nested/section/page.md';
    $scanner = new DocsClassReferenceScanner($packagesRoot, ['Marko\Fictional\\']);

    expect(array_column($scanner->findUnresolved([$page]), 'class'))->toBe(['Marko\Missing\Fqcn']);
});
