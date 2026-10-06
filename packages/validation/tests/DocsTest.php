<?php

declare(strict_types=1);

function validationDocsPage(): string
{
    return (string) file_get_contents(dirname(__DIR__, 2) . '/docs-markdown/docs/packages/validation.md');
}

it('documents wildcard keys on the validation docs page', function (): void {
    expect(validationDocsPage())->toContain('### Validating Arrays with Wildcards')
        ->and(validationDocsPage())->toContain("'items.*.name'")
        ->and(validationDocsPage())->toContain("'matrix.*.*'")
        ->and(validationDocsPage())->toContain('### Multi-File Uploads')
        ->and(validationDocsPage())->toContain("'photos.*' => 'image|max_size:2048'")
        ->and(validationDocsPage())->toContain('same:items.*.sku');
});

it('no longer says per-file rules are unsupported', function (): void {
    expect(validationDocsPage())->not->toContain('not supported yet');
});
