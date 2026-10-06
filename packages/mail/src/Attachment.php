<?php

declare(strict_types=1);

namespace Marko\Mail;

use Marko\Mail\Exceptions\MessageException;

readonly class Attachment
{
    /**
     * RFC 2045 `type/subtype`, each side an RFC 2045 token (no parameters).
     */
    private const string MIME_TYPE_PATTERN = '~^[!#$%&\'*+\-.^_`|\~0-9A-Za-z]+/[!#$%&\'*+\-.^_`|\~0-9A-Za-z]+$~';

    /**
     * RFC 5322 msg-id characters: atext plus "." and "@" (the angle brackets are added on render).
     */
    private const string CONTENT_ID_PATTERN = '~^[!#$%&\'*+\-/=?^_`{|}\~.@0-9A-Za-z]+$~';

    /**
     * @throws MessageException
     */
    private function __construct(
        public string $content,
        public string $name,
        public string $mimeType,
        public ?string $contentId = null,
    ) {
        self::assertNoHeaderBreak('attachment name', $name);
        self::assertNoHeaderBreak('attachment mime type', $mimeType);

        if (!preg_match(self::MIME_TYPE_PATTERN, $mimeType)) {
            throw MessageException::invalidAttachmentMimeType($mimeType);
        }

        if ($contentId !== null) {
            self::assertNoHeaderBreak('attachment content ID', $contentId);

            if (!preg_match(self::CONTENT_ID_PATTERN, $contentId)) {
                throw MessageException::invalidAttachmentContentId($contentId);
            }
        }
    }

    /**
     * @throws MessageException
     */
    public static function fromPath(
        string $path,
        ?string $name = null,
        ?string $mimeType = null,
    ): self {
        if (!file_exists($path)) {
            throw MessageException::attachmentNotFound($path);
        }

        return new self(
            content: file_get_contents($path),
            name: $name ?? basename($path),
            mimeType: $mimeType ?? mime_content_type($path) ?: 'application/octet-stream',
        );
    }

    /**
     * @throws MessageException
     */
    public static function fromContent(
        string $content,
        string $name,
        string $mimeType = 'application/octet-stream',
    ): self {
        return new self(
            content: $content,
            name: $name,
            mimeType: $mimeType,
        );
    }

    /**
     * @throws MessageException
     */
    public static function inline(
        string $path,
        string $contentId,
        ?string $name = null,
        ?string $mimeType = null,
    ): self {
        if (!file_exists($path)) {
            throw MessageException::attachmentNotFound($path);
        }

        return new self(
            content: file_get_contents($path),
            name: $name ?? basename($path),
            mimeType: $mimeType ?? mime_content_type($path) ?: 'application/octet-stream',
            contentId: $contentId,
        );
    }

    /**
     * @throws MessageException
     */
    private static function assertNoHeaderBreak(
        string $field,
        string $value,
    ): void {
        if (strpbrk($value, "\r\n\0") !== false) {
            throw MessageException::headerInjection($field, $value);
        }
    }
}
