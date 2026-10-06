<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use Marko\Core\Contracts\UploadedFileInterface;
use Marko\Validation\Contracts\RuleInterface;

readonly class Max implements RuleInterface
{
    public function __construct(
        private int|float $maximum,
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
            return count($value) <= $this->maximum;
        }

        if (is_numeric($value)) {
            return (float) $value <= $this->maximum;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $this->maximum;
        }

        return false;
    }

    public function message(
        string $field,
        mixed $value,
    ): string {
        if ($value instanceof UploadedFileInterface) {
            return "The $field field is a file: use max_size:$this->maximum to limit its size in kilobytes.";
        }

        if (is_array($value)) {
            return "The $field field must not have more than $this->maximum items.";
        }

        if (is_numeric($value)) {
            return "The $field field must not exceed $this->maximum.";
        }

        if (is_string($value)) {
            return "The $field field must not exceed $this->maximum characters.";
        }

        return "The $field field must not exceed $this->maximum.";
    }
}
