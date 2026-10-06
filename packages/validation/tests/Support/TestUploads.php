<?php

declare(strict_types=1);

namespace Marko\Validation\Tests\Support;

use Marko\Routing\Http\UploadedFile;

/**
 * Builds real uploaded files on disk so the rules sniff genuine contents with finfo.
 */
class TestUploads
{
    /** A 1x1 transparent PNG. */
    private const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    /** A 1x1 GIF. */
    private const string GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    /** A JFIF header: enough for finfo to report image/jpeg. */
    private const string JPEG = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9";

    private const string SVG = '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><script>alert(1)</script></svg>';

    private const string PDF = "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";

    public static function png(
        string $clientFilename = 'avatar.png',
        string $clientMediaType = 'image/png',
    ): UploadedFile {
        return self::make((string) base64_decode(self::PNG), $clientFilename, $clientMediaType);
    }

    public static function jpeg(): UploadedFile
    {
        return self::make(self::JPEG, 'photo.jpeg', 'image/jpeg');
    }

    public static function gif(): UploadedFile
    {
        return self::make((string) base64_decode(self::GIF), 'photo.gif', 'image/gif');
    }

    public static function svg(): UploadedFile
    {
        return self::make(self::SVG, 'logo.svg', 'image/svg+xml');
    }

    public static function pdf(): UploadedFile
    {
        return self::make(self::PDF, 'invoice.pdf', 'application/pdf');
    }

    /**
     * Plain text by default; pass a lying client filename and media type to impersonate another type.
     */
    public static function text(
        string $clientFilename = 'notes.txt',
        string $clientMediaType = 'text/plain',
    ): UploadedFile {
        return self::make('just some plain text', $clientFilename, $clientMediaType);
    }

    /**
     * A file of exactly $bytes bytes.
     */
    public static function sized(
        int $bytes,
    ): UploadedFile {
        return self::make(str_repeat('a', $bytes), 'data.txt', 'text/plain');
    }

    /**
     * An upload PHP reported as failed (a PNG on disk, so only the error code can reject it).
     */
    public static function failed(
        int $error = UPLOAD_ERR_INI_SIZE,
    ): UploadedFile {
        return self::make((string) base64_decode(self::PNG), 'avatar.png', 'image/png', $error);
    }

    private static function make(
        string $contents,
        string $clientFilename,
        string $clientMediaType,
        int $error = UPLOAD_ERR_OK,
    ): UploadedFile {
        $path = (string) tempnam(sys_get_temp_dir(), 'marko-validation-upload-');
        file_put_contents($path, $contents);

        return new UploadedFile(
            tempPath: $path,
            clientFilename: $clientFilename,
            clientMediaType: $clientMediaType,
            size: strlen($contents),
            error: $error,
        );
    }
}
