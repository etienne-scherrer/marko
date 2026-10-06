<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use Marko\Core\Contracts\UploadedFileInterface;
use Marko\Core\Exceptions\MarkoException;

/**
 * The value is an uploaded raster image, judged by the MIME type sniffed from its contents.
 *
 * SVG is deliberately excluded: it is XML that can carry script. Accept it explicitly with
 * `mimes:svg` or `mimetypes:image/svg+xml` when you serve it safely.
 */
readonly class Image extends AbstractFileRule
{
    private const array MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * @throws MarkoException
     */
    protected function passesFile(
        UploadedFileInterface $file,
    ): bool {
        return in_array($file->mimeType(), self::MIME_TYPES, true);
    }

    protected function fileMessage(
        string $field,
    ): string {
        return "The $field field must be an image (JPEG, PNG, GIF or WebP).";
    }
}
