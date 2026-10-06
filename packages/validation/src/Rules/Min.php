<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use Marko\Core\Contracts\UploadedFileInterface;
use Marko\Validation\Contracts\RuleInterface;

readonly class Min implements RuleInterface
{
    public function __construct(
        private int|float $minimum,
    ) {}

    public function passes(
        string $field,
        mixed $value,
        array $data,
    ): bool {
        if ($value === null || $value === '') {
            return true;
        }

        // Files are sized with max_size/min_size; message() says so.
        if ($value instanceof UploadedFileInterface) {
            return false;
        }

        if (is_array($value)) {
            return count($value) >= $this->minimum;
        }

        if (is_numeric($value)) {
            return (float) $value >= $this->minimum;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $this->minimum;
        }

        return false;
    }

    public function message(
        string $field,
        mixed $value,
    ): string {
        if ($value instanceof UploadedFileInterface) {
            return "The $field field is a file: use min_size:$this->minimum to require a minimum size in kilobytes.";
        }

        if (is_array($value)) {
            return "The $field field must have at least $this->minimum items.";
        }

        if (is_numeric($value)) {
            return "The $field field must be at least $this->minimum.";
        }

        if (is_string($value)) {
            return "The $field field must be at least $this->minimum characters.";
        }

        return "The $field field must be at least $this->minimum.";
    }
}
