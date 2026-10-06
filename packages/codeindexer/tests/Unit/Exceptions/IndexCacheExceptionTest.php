<?php

declare(strict_types=1);

use Marko\CodeIndexer\Exceptions\IndexCacheException;

it('includes the reason in cacheDirUnwritable context when one is given', function (): void {
    $exception = IndexCacheException::cacheDirUnwritable('/app/.marko', 'mkdir(): Permission denied');

    expect($exception->getMessage())->toBe('Cannot write to index cache: /app/.marko')
        ->and($exception->getContext())
        ->toBe('While building or saving the codeindexer cache: mkdir(): Permission denied');
});

it('keeps the cacheDirUnwritable context unchanged without a reason', function (): void {
    $exception = IndexCacheException::cacheDirUnwritable('/app/.marko');

    expect($exception->getContext())->toBe('While building or saving the codeindexer cache');
});

it('includes the reason in cacheNotRemovable context when one is given', function (): void {
    $exception = IndexCacheException::cacheNotRemovable('/app/.marko/index.cache', 'unlink(): Permission denied');

    expect($exception->getMessage())->toBe('Cannot remove index cache: /app/.marko/index.cache')
        ->and($exception->getContext())
        ->toBe('While invalidating the codeindexer cache: unlink(): Permission denied')
        ->and($exception->getSuggestion())->toContain('.marko');
});
