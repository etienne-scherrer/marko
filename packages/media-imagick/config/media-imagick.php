<?php

declare(strict_types=1);

return [
    // Formats accepted as input (detected from magic bytes before ImageMagick
    // reads the file) and as convert() targets. Only raster formats the
    // sniffer in marko/media recognises can be listed: JPEG, PNG, GIF, WEBP,
    // AVIF, HEIC, TIFF, BMP.
    'allowed_raster_formats' => ['JPEG', 'PNG', 'GIF', 'WEBP', 'AVIF'],

    // ImageMagick resource limits, applied before every read. Images larger
    // than max_width x max_height or max_pixels are rejected before decoding.
    'limits' => [
        'max_width' => 16384,
        'max_height' => 16384,
        'max_pixels' => 50_000_000,
        'memory_bytes' => 268_435_456,
        'time_seconds' => 60,
    ],
];
