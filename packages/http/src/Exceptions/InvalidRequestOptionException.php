<?php

declare(strict_types=1);

namespace Marko\Http\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class InvalidRequestOptionException extends MarkoException
{
    /**
     * @param array<string> $supported
     */
    public static function unknownOption(
        string $key,
        array $supported,
    ): self {
        return new self(
            message: "Unknown HTTP request option '$key'.",
            context: "The option '$key' was passed to the HTTP client but is not a supported request option.",
            suggestion: 'Use one of the supported options: ' . implode(', ', $supported)
                . '. Check for typos, or use a driver-specific escape hatch for non-portable options.',
        );
    }

    /**
     * @param array<string> $keys
     */
    public static function conflictingBodies(
        array $keys,
    ): self {
        $list = implode("', '", $keys);

        return new self(
            message: "Only one body option may be given per request; got '$list'.",
            context: 'The body options (body, json, form_params, multipart) are mutually exclusive.',
            suggestion: 'Pass exactly one of body, json, form_params or multipart.',
        );
    }

    public static function invalidAuth(): self
    {
        return new self(
            message: "Invalid value for HTTP request option 'auth'.",
            context: "The 'auth' option must be a basic credential pair or a bearer token.",
            suggestion: "Use ['username', 'password'] for basic auth or ['bearer' => 'token'] for a bearer token.",
        );
    }

    public static function invalidType(
        string $key,
        string $expected,
        mixed $value,
    ): self {
        $actual = get_debug_type($value);

        return new self(
            message: "Invalid value for HTTP request option '$key': expected $expected, got $actual.",
            context: "The '$key' option was passed a value of type $actual.",
            suggestion: "Pass $expected for the '$key' option.",
        );
    }
}
