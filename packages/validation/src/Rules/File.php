<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use Marko\Core\Contracts\UploadedFileInterface;

/**
 * The value is an uploaded file that arrived without an upload error and has not been moved.
 */
readonly class File extends AbstractFileRule
{
    protected function passesFile(
        UploadedFileInterface $file,
    ): bool {
        return true;
    }

    protected function fileMessage(
        string $field,
    ): string {
        return "The $field field must be a file.";
    }
}
