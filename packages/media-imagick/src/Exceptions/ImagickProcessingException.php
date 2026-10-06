<?php

declare(strict_types=1);

namespace Marko\MediaImagick\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class ImagickProcessingException extends MarkoException
{
    /**
     * @param array<string> $allowedFormats
     */
    public static function formatNotAllowed(
        string $format,
        array $allowedFormats,
    ): self {
        $allowedList = implode(', ', $allowedFormats);

        return new self(
            message: "Image format '$format' is not in the raster allowlist",
            context: "Attempted to process an image with format: $format",
            suggestion: "Use one of the allowed raster formats: $allowedList",
        );
    }

    public static function imageTooLarge(
        string $imagePath,
        int $width,
        int $height,
        int $maxWidth,
        int $maxHeight,
        int $maxPixels,
    ): self {
        return new self(
            message: "Image dimensions {$width}x$height exceed the configured limits",
            context: "While reading image at path: $imagePath (limits: max {$maxWidth}x$maxHeight, $maxPixels pixels)",
            suggestion: 'Reject the upload, or raise media-imagick.limits.max_width, max_height or max_pixels '
                . 'if images this large are expected',
        );
    }

    public static function processingFailed(
        string $operation,
        string $imagePath,
        string $hint,
    ): self {
        return new self(
            message: "Failed to $operation image",
            context: "While processing image at path: $imagePath",
            suggestion: $hint,
        );
    }
}
