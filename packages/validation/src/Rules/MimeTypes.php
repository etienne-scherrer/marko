<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use InvalidArgumentException;
use Marko\Core\Contracts\UploadedFileInterface;
use Marko\Core\Exceptions\MarkoException;

/**
 * The MIME type sniffed from the file contents matches one of the listed types.
 * A `type/*` entry matches every subtype (`image/*`).
 *
 * The media type the client sent is never consulted.
 */
readonly class MimeTypes extends AbstractFileRule
{
    /**
     * @var array<string>
     */
    private array $mimeTypes;

    /**
     * @throws InvalidArgumentException when no MIME types are given
     */
    public function __construct(
        string ...$mimeTypes,
    ) {
        $normalized = array_values(array_filter(array_map(
            fn (string $mimeType): string => strtolower(trim($mimeType)),
            $mimeTypes,
        )));

        if ($normalized === []) {
            throw new InvalidArgumentException(
                'The mimetypes rule needs at least one MIME type, e.g. mimetypes:image/*,application/pdf',
            );
        }

        $this->mimeTypes = $normalized;
    }

    /**
     * @throws MarkoException
     */
    protected function passesFile(
        UploadedFileInterface $file,
    ): bool {
        $mimeType = strtolower($file->mimeType());

        return array_any(
            $this->mimeTypes,
            fn (string $allowed): bool => $allowed === $mimeType
                || (str_ends_with($allowed, '/*') && str_starts_with($mimeType, substr($allowed, 0, -1))),
        );
    }

    protected function fileMessage(
        string $field,
    ): string {
        return "The $field field must be a file of type: " . implode(', ', $this->mimeTypes) . '.';
    }
}
