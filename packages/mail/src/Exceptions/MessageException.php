<?php

declare(strict_types=1);

namespace Marko\Mail\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class MessageException extends MarkoException
{
    public static function invalidEmailAddress(
        string $email,
    ): self {
        return new self(
            message: "Invalid email address: '$email'",
            context: "While validating email address '$email'",
            suggestion: 'Provide a valid email address in the format user@domain.com',
        );
    }

    public static function headerInjection(
        string $field,
        string $value,
    ): self {
        $safe = addcslashes($value, "\r\n\0");

        return new self(
            message: "Header injection attempt detected in $field: '$safe'",
            context: "While setting $field to value containing CR, LF or NUL characters",
            suggestion: 'Remove carriage return (\\r), line feed (\\n) and NUL (\\0) characters from the value',
        );
    }

    public static function attachmentNotFound(
        string $path,
    ): self {
        return new self(
            message: "Attachment file not found: '$path'",
            context: "Attempted to attach file: $path",
            suggestion: 'Verify the file path exists and is readable.',
        );
    }

    public static function invalidAttachmentMimeType(
        string $mimeType,
    ): self {
        return new self(
            message: "Invalid attachment mime type: '$mimeType'",
            context: "While creating an attachment with mime type '$mimeType'",
            suggestion: "Use a bare 'type/subtype' value such as 'application/pdf', without parameters, spaces or quotes",
        );
    }

    public static function invalidAttachmentContentId(
        string $contentId,
    ): self {
        return new self(
            message: "Invalid attachment content ID: '$contentId'",
            context: "While creating an inline attachment with content ID '$contentId'",
            suggestion: "Use only letters, digits, '.', '@' and the RFC 5322 atext symbols (!#$%&'*+-/=?^_`{|}~), without angle brackets or spaces",
        );
    }
}
