<?php

declare(strict_types=1);

namespace Marko\Tests\Support\DocsClassReference;

use RuntimeException;

/**
 * Finds `Marko\...` class references inside fenced PHP code blocks of Markdown
 * docs and reports the ones that do not resolve to a file through the PSR-4
 * maps declared in `packages/*\/composer.json`.
 */
class DocsClassReferenceScanner
{
    /**
     * @var array<string, list<string>> namespace prefix => absolute source directories
     */
    private array $psr4Map = [];

    /**
     * @param list<string> $allowedPrefixes Namespace prefixes of intentionally fictional examples
     */
    public function __construct(
        string $packagesRoot,
        private array $allowedPrefixes = [],
    ) {
        $this->psr4Map = $this->buildPsr4Map($packagesRoot);
    }

    /**
     * Discover every package README and every Markdown file under the docs-markdown docs tree.
     *
     * @return list<string>
     */
    public static function discoverMarkdownFiles(string $packagesRoot): array
    {
        $files = glob($packagesRoot . '/*/README.md') ?: [];

        $docsRoot = $packagesRoot . '/docs-markdown/docs';

        if (is_dir($docsRoot)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($docsRoot, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && in_array($file->getExtension(), ['md', 'mdx'], true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return array_values($files);
    }

    /**
     * Extract every `Marko\...` reference found inside fenced PHP code blocks.
     *
     * @return list<array{file: string, line: int, class: string}>
     */
    public function extractReferences(string $file): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException("Unable to read Markdown file: $file");
        }

        $references = [];
        $inPhpBlock = false;
        $fence = '';

        foreach ($lines as $index => $line) {
            $trimmed = ltrim($line);

            if (!$inPhpBlock) {
                if (preg_match('/^(`{3,}|~{3,})\s*php\b/i', $trimmed, $match) === 1) {
                    $inPhpBlock = true;
                    $fence = $match[1];
                }

                continue;
            }

            if (str_starts_with($trimmed, $fence) && trim(substr($trimmed, strlen($fence))) === '') {
                $inPhpBlock = false;

                continue;
            }

            foreach ($this->referencesOnLine($line) as $class) {
                $references[] = ['file' => $file, 'line' => $index + 1, 'class' => $class];
            }
        }

        return $references;
    }

    /**
     * Return every reference in the given files that neither resolves through PSR-4 nor is allowlisted.
     *
     * @param list<string> $files
     * @return list<array{file: string, line: int, class: string}>
     */
    public function findUnresolved(array $files): array
    {
        $unresolved = [];

        foreach ($files as $file) {
            foreach ($this->extractReferences($file) as $reference) {
                if ($this->isAllowed($reference['class']) || $this->resolves($reference['class'])) {
                    continue;
                }

                $unresolved[] = $reference;
            }
        }

        return $unresolved;
    }

    public function resolves(string $class): bool
    {
        foreach ($this->psr4Map as $prefix => $directories) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

            foreach ($directories as $directory) {
                if (is_file($directory . '/' . $relative)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isAllowed(string $class): bool
    {
        foreach ($this->allowedPrefixes as $prefix) {
            if (str_starts_with($class, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function referencesOnLine(string $line): array
    {
        $code = ltrim($line);

        // Namespace declarations name a namespace, not a class. `use function`
        // and `use const` import functions and constants, which PSR-4 does not map.
        if (preg_match('/^(namespace\s|use\s+(function|const)\s)/i', $code) === 1) {
            return [];
        }

        // Group use: `use Marko\Routing\Attributes\{Get, Post};`
        if (preg_match('/^use\s+\\\\?(Marko\\\\[A-Za-z0-9_\\\\]+)\\\\\{([^}]*)\}/', $code, $match) === 1) {
            $classes = [];

            foreach (explode(',', $match[2]) as $member) {
                $member = trim((string) preg_replace('/\s+as\s+\w+$/i', '', trim($member)));

                if ($member !== '') {
                    $classes[] = $match[1] . '\\' . $member;
                }
            }

            return $classes;
        }

        preg_match_all('/(?<![A-Za-z0-9_\\\\])\\\\?(Marko(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+)/', $line, $matches);

        $classes = [];

        foreach ($matches[1] as $class) {
            $classes[] = $class;
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return array<string, list<string>>
     */
    private function buildPsr4Map(string $packagesRoot): array
    {
        $map = [];

        foreach (glob($packagesRoot . '/*/composer.json') ?: [] as $composerFile) {
            $contents = file_get_contents($composerFile);

            if ($contents === false) {
                throw new RuntimeException("Unable to read $composerFile");
            }

            /** @var array{autoload?: array{psr-4?: array<string, string|list<string>>}} $composer */
            $composer = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            foreach ($composer['autoload']['psr-4'] ?? [] as $prefix => $paths) {
                foreach ((array) $paths as $path) {
                    $map[$prefix][] = rtrim(dirname($composerFile) . '/' . $path, '/');
                }
            }
        }

        return $map;
    }
}
