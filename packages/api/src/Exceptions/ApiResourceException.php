<?php

declare(strict_types=1);

namespace Marko\Api\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class ApiResourceException extends MarkoException
{
    /**
     * @param class-string $valueClass
     */
    public static function unresolvedValue(
        string $valueClass,
    ): self {
        $shortName = substr((string) strrchr('\\' . $valueClass, '\\'), 1);

        return new self(
            message: "Refusing to JSON-encode an unresolved $shortName",
            context: "A $valueClass reached json_encode() without passing through JsonResource filtering, so a field meant to be conditional or omitted would have been serialized.",
            suggestion: 'Serialize resources through JsonResource::resolve(), JsonResource::toResponse(), ResourceCollection::toArray() or ResourceCollection::toResponse() instead of encoding raw toArray() output.',
        );
    }
}
