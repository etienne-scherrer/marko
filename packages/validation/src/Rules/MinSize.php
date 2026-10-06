<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use Marko\Core\Contracts\UploadedFileInterface;

/**
 * The uploaded file is at least the given size in kilobytes (1 KB = 1024 bytes).
 */
readonly class MinSize extends AbstractFileRule
{
    public function __construct(
        private int|float $kilobytes,
    ) {}

    protected function passesFile(
        UploadedFileInterface $file,
    ): bool {
        return $file->size() / 1024 >= $this->kilobytes;
    }

    protected function fileMessage(
        string $field,
    ): string {
        return "The $field field must be at least $this->kilobytes kilobytes.";
    }
}
