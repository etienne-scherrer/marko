<?php

declare(strict_types=1);

namespace Marko\Filesystem\Exceptions;

class PathException extends FilesystemException
{
    public static function traversalAttempt(
        string $path,
    ): self {
        return new self(
            message: 'Path traversal attempt detected',
            context: "Attempted path: $path",
            suggestion: "Use paths relative to the disk root without '..' sequences",
        );
    }

    public static function rootDeletion(
        string $path,
    ): self {
        return new self(
            message: 'Refusing to delete the disk root',
            context: "Path '$path' resolves to the root of the disk",
            suggestion: 'Pass the name of a subdirectory to deleteDirectory(); to clear a disk, delete its entries individually',
        );
    }

    public static function outsideRoot(
        string $path,
    ): self {
        return new self(
            message: 'Path resolves outside the disk root',
            context: "Path '$path' follows a symbolic link that points outside the disk root",
            suggestion: 'Remove the symbolic link, or configure a disk whose root contains the link target',
        );
    }

    public static function invalidPath(
        string $path,
        string $reason,
    ): self {
        return new self(
            message: "Invalid path: '$path'",
            context: $reason,
            suggestion: 'Provide a valid filesystem path',
        );
    }
}
