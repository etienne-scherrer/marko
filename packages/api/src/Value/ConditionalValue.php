<?php

declare(strict_types=1);

namespace Marko\Api\Value;

use JsonSerializable;
use Marko\Api\Exceptions\ApiResourceException;

readonly class ConditionalValue implements JsonSerializable
{
    public function __construct(
        public bool $condition,
        public mixed $value,
    ) {}

    /**
     * Resolve the value: returns the wrapped value if condition is true,
     * or a MissingValue sentinel if condition is false.
     */
    public function resolve(): mixed
    {
        if ($this->condition) {
            return $this->value;
        }

        return new MissingValue();
    }

    /**
     * Refuse direct serialization so a hidden value can never leak.
     *
     * @throws ApiResourceException
     */
    public function jsonSerialize(): never
    {
        throw ApiResourceException::unresolvedValue(self::class);
    }
}
