<?php

declare(strict_types=1);

namespace Marko\Api\Value;

use JsonSerializable;
use Marko\Api\Exceptions\ApiResourceException;

class MissingValue implements JsonSerializable
{
    /**
     * Refuse direct serialization so an omitted field can never appear in output.
     *
     * @throws ApiResourceException
     */
    public function jsonSerialize(): never
    {
        throw ApiResourceException::unresolvedValue(self::class);
    }
}
