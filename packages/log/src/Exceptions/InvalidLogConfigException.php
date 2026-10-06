<?php

declare(strict_types=1);

namespace Marko\Log\Exceptions;

class InvalidLogConfigException extends LogException
{
    public static function invalidMode(
        string $key,
        int $mode,
    ): self {
        $given = sprintf('0%o', $mode);

        return new self(
            message: "Invalid permission mode $given for '$key'",
            context: "Configured value: $mode (octal $given)",
            suggestion: "Set '$key' to an octal permission mode between 0 and 0777, e.g. 0600 for files or 0700 for directories",
        );
    }

    public static function invalidRedactKey(
        mixed $key,
    ): self {
        $type = get_debug_type($key);

        return new self(
            message: "Invalid entry in 'log.redact_keys': expected a non-empty string, got $type",
            context: "Configured entry type: $type",
            suggestion: "List context keys to redact as non-empty strings, e.g. ['password', 'token']",
        );
    }
}
