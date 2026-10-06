<?php

declare(strict_types=1);

namespace Marko\CodeIndexer\Contract;

use Marko\CodeIndexer\Exceptions\IndexCacheException;

interface IndexCacheInterface
{
    public function get(string $key): mixed;

    public function set(
        string $key,
        mixed $value,
    ): void;

    public function has(string $key): bool;

    /** @throws IndexCacheException When the on-disk cache exists but cannot be removed */
    public function invalidate(): void;
}
