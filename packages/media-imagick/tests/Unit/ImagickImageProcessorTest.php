<?php

declare(strict_types=1);

use Marko\Config\Exceptions\ConfigNotFoundException;
use Marko\Media\Exceptions\ImageFormatException;
use Marko\MediaImagick\Config\ImagickConfig;
use Marko\MediaImagick\Driver\ImagickImageProcessor;
use Marko\MediaImagick\Exceptions\ImagickProcessingException;
use Marko\Testing\Fake\FakeConfigRepository;

function makeImagickConfig(
    array $allowedRasterFormats = ['JPEG', 'PNG', 'GIF', 'WEBP'],
    int $maxWidth = 16384,
    int $maxHeight = 16384,
    int $maxPixels = 50_000_000,
): ImagickConfig {
    $configRepo = new FakeConfigRepository([
        'media-imagick.allowed_raster_formats' => $allowedRasterFormats,
        'media-imagick.limits.max_width' => $maxWidth,
        'media-imagick.limits.max_height' => $maxHeight,
        'media-imagick.limits.max_pixels' => $maxPixels,
        'media-imagick.limits.memory_bytes' => 268_435_456,
        'media-imagick.limits.time_seconds' => 60,
    ]);

    return new ImagickConfig($configRepo);
}

function makeImagickFixture(
    int $width,
    int $height,
    string $format = 'png',
): string {
    $imagePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.' . $format;
    $imagick = new Imagick();
    $imagick->newImage($width, $height, 'white');
    $imagick->setImageFormat($format);
    $imagick->writeImage($imagePath);
    $imagick->clear();

    return $imagePath;
}

describe('ImagickImageProcessor', function (): void {
    beforeEach(function (): void {
        // Resource limits are process-wide; reset them so one test's limits never leak into the next.
        if (class_exists('Imagick')) {
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_WIDTH, 16384);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_HEIGHT, 16384);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_AREA, 50_000_000);
        }
    });

    it('resizes an image to specified width and height', function (): void {
        $imagePath = makeImagickFixture(100, 100);

        $processor = new ImagickImageProcessor(makeImagickConfig());
        $result = $processor->resize($imagePath, 50, 50, false);

        $resultImagick = new Imagick($result);

        expect($resultImagick->getImageWidth())->toBe(50)
            ->and($resultImagick->getImageHeight())->toBe(50);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('crops an image to specified region coordinates', function (): void {
        $imagePath = makeImagickFixture(100, 100);

        $processor = new ImagickImageProcessor(makeImagickConfig());
        $result = $processor->crop($imagePath, 10, 10, 40, 30);

        $resultImagick = new Imagick($result);

        expect($resultImagick->getImageWidth())->toBe(40)
            ->and($resultImagick->getImageHeight())->toBe(30);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('converts image format between JPEG, PNG, WebP, GIF, and AVIF', function (): void {
        $imagePath = makeImagickFixture(50, 50);

        $processor = new ImagickImageProcessor(makeImagickConfig());
        $result = $processor->convert($imagePath, 'jpeg');

        $resultImagick = new Imagick($result);

        expect(strtolower($resultImagick->getImageFormat()))->toBe('jpeg');

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('normalises the jpg alias to JPEG when converting', function (): void {
        $imagePath = makeImagickFixture(20, 20);

        $processor = new ImagickImageProcessor(makeImagickConfig(allowedRasterFormats: ['PNG', 'JPEG']));
        $result = $processor->convert($imagePath, 'jpg');

        expect(strtoupper((new Imagick($result))->getImageFormat()))->toBe('JPEG')
            ->and($result)->toEndWith('.jpeg');

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('rejects a convert target format that is not in the raster allowlist', function (string $format): void {
        $imagePath = makeImagickFixture(20, 20);
        $processor = new ImagickImageProcessor(makeImagickConfig(allowedRasterFormats: ['PNG', 'JPEG']));

        try {
            expect(fn () => $processor->convert($imagePath, $format))
                ->toThrow(ImagickProcessingException::class, 'is not in the raster allowlist');
        } finally {
            @unlink($imagePath);
        }
    })->with(['svg', 'msl', 'ps', 'webp', 'png:/tmp/evil'])
        ->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('generates thumbnail at specified maximum dimension', function (): void {
        $imagePath = makeImagickFixture(200, 100);

        $processor = new ImagickImageProcessor(makeImagickConfig());
        $result = $processor->thumbnail($imagePath, 80);

        $resultImagick = new Imagick($result);

        expect($resultImagick->getImageWidth())->toBeLessThanOrEqual(80)
            ->and($resultImagick->getImageHeight())->toBeLessThanOrEqual(80);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('preserves aspect ratio during resize when requested', function (): void {
        $imagePath = makeImagickFixture(200, 100);

        $processor = new ImagickImageProcessor(makeImagickConfig());
        $result = $processor->resize($imagePath, 80, 80, true);

        $resultImagick = new Imagick($result);
        $width = $resultImagick->getImageWidth();
        $height = $resultImagick->getImageHeight();

        expect($width)->toBeLessThanOrEqual(80)
            ->and($height)->toBeLessThanOrEqual(80)
            ->and($width)->toBeGreaterThan($height);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('throws ImagickProcessingException when Imagick extension is unavailable', function (): void {
        expect(fn () => new ImagickImageProcessor(makeImagickConfig()))
            ->toThrow(ImagickProcessingException::class, 'Imagick extension is not available');
    })->skip(class_exists('Imagick'), 'Imagick extension is available');

    it('rejects SVG input before ImageMagick decodes it', function (): void {
        $processor = new ImagickImageProcessor(makeImagickConfig(allowedRasterFormats: ['JPEG', 'PNG']));

        $imagePath = sys_get_temp_dir() . '/' . uniqid('test_svg_', true) . '.svg';
        file_put_contents($imagePath, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>');

        try {
            expect(fn () => $processor->resize($imagePath, 10, 10))
                ->toThrow(ImageFormatException::class, 'not a recognised raster image');
        } finally {
            @unlink($imagePath);
        }
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('never hands a non-raster payload disguised with a raster extension to an ImageMagick coder', function (
        string $payload,
    ): void {
        $processor = new ImagickImageProcessor(makeImagickConfig());
        $marker = sys_get_temp_dir() . '/' . uniqid('marko_imagick_marker_', true) . '.png';
        $imagePath = sys_get_temp_dir() . '/' . uniqid('test_disguised_', true) . '.png';
        file_put_contents($imagePath, str_replace('{marker}', $marker, $payload));

        try {
            expect(fn () => $processor->thumbnail($imagePath, 10))
                ->toThrow(ImageFormatException::class, 'not a recognised raster image')
                ->and(file_exists($marker))->toBeFalse();
        } finally {
            @unlink($imagePath);

            if (file_exists($marker)) {
                unlink($marker);
            }
        }
    })->with([
        'MVG' => ["push graphic-context\nviewbox 0 0 10 10\nfill 'red'\nrectangle 0,0 10,10\npop graphic-context\n"],
        'MSL writing a file' => [
            '<?xml version="1.0" encoding="UTF-8"?><image><read filename="rose:"/><write filename="{marker}"/></image>',
        ],
        'SVG with external reference' => [
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10" '
            . 'height="10"><image xlink:href="file:///etc/passwd" width="10" height="10"/></svg>',
        ],
    ])->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('rejects image paths carrying an ImageMagick coder prefix', function (string $path): void {
        $processor = new ImagickImageProcessor(makeImagickConfig());

        expect(fn () => $processor->resize($path, 10, 10))
            ->toThrow(ImageFormatException::class, 'coder prefix or stream wrapper');
    })->with(['msl:/tmp/x.msl', 'url:http://127.0.0.1/x.png', 'rose:', 'PNG:/tmp/x.png'])
        ->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('rejects a detected raster format that is not in the raster allowlist', function (): void {
        $imagePath = makeImagickFixture(10, 10, 'gif');
        $processor = new ImagickImageProcessor(makeImagickConfig(allowedRasterFormats: ['JPEG', 'PNG']));

        try {
            expect(fn () => $processor->resize($imagePath, 5, 5))
                ->toThrow(ImagickProcessingException::class, "Image format 'GIF' is not in the raster allowlist");
        } finally {
            @unlink($imagePath);
        }
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('reads by detected content, not file extension', function (): void {
        $pngPath = makeImagickFixture(30, 20);
        $disguisedPath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.gif';
        rename($pngPath, $disguisedPath);

        $processor = new ImagickImageProcessor(makeImagickConfig(allowedRasterFormats: ['PNG']));
        $result = $processor->crop($disguisedPath, 0, 0, 10, 10);

        expect(strtoupper((new Imagick($result))->getImageFormat()))->toBe('PNG');

        @unlink($disguisedPath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('rejects images whose header dimensions exceed the configured limits before decoding', function (
        int $maxWidth,
        int $maxHeight,
        int $maxPixels,
    ): void {
        $imagePath = makeImagickFixture(100, 80);
        $processor = new ImagickImageProcessor(makeImagickConfig(
            maxWidth: $maxWidth,
            maxHeight: $maxHeight,
            maxPixels: $maxPixels,
        ));

        try {
            expect(fn () => $processor->resize($imagePath, 10, 10))
                ->toThrow(ImagickProcessingException::class, 'Image dimensions 100x80 exceed the configured limits');
        } finally {
            @unlink($imagePath);
        }
    })->with([
        'width' => [99, 16384, 50_000_000],
        'height' => [16384, 79, 50_000_000],
        'area' => [16384, 16384, 7_999],
    ])->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('applies the configured resource limits to ImageMagick before reading', function (): void {
        $imagePath = makeImagickFixture(10, 10);
        $processor = new ImagickImageProcessor(
            makeImagickConfig(maxWidth: 4000, maxHeight: 3000, maxPixels: 9_000_000),
        );
        $result = $processor->thumbnail($imagePath, 5);

        expect(Imagick::getResourceLimit(Imagick::RESOURCETYPE_WIDTH))->toEqual(4000)
            ->and(Imagick::getResourceLimit(Imagick::RESOURCETYPE_HEIGHT))->toEqual(3000)
            ->and(Imagick::getResourceLimit(Imagick::RESOURCETYPE_AREA))->toEqual(9_000_000)
            ->and(Imagick::getResourceLimit(Imagick::RESOURCETYPE_MEMORY))->toEqual(268_435_456)
            ->and(Imagick::getResourceLimit(Imagick::RESOURCETYPE_TIME))->toEqual(60);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');

    it('processes and writes an allowlisted raster image successfully', function (): void {
        $config = makeImagickConfig(allowedRasterFormats: ['JPEG', 'PNG', 'GIF', 'WEBP']);
        $processor = new ImagickImageProcessor($config);

        $imagePath = makeImagickFixture(100, 100);

        $result = $processor->resize($imagePath, 50, 50, false);

        $resultImagick = new Imagick($result);

        expect($resultImagick->getImageWidth())->toBe(50)
            ->and($resultImagick->getImageHeight())->toBe(50);

        @unlink($imagePath);
        @unlink($result);
    })->skip(!class_exists('Imagick'), 'Imagick extension not available');
});

describe('ImagickConfig', function (): void {
    it(
        'reads the Imagick raster allowlist from configuration and throws ConfigNotFoundException when the key is missing',
        function (): void {
            $config = new ImagickConfig(new FakeConfigRepository([]));

            expect(fn () => $config->allowedRasterFormats())
                ->toThrow(ConfigNotFoundException::class);
        },
    );

    it('upper-cases configured raster formats', function (): void {
        $config = new ImagickConfig(new FakeConfigRepository([
            'media-imagick.allowed_raster_formats' => ['jpeg', 'Png'],
        ]));

        expect($config->allowedRasterFormats())->toBe(['JPEG', 'PNG']);
    });

    it('reads resource limits from configuration', function (): void {
        $config = makeImagickConfig(maxWidth: 1000, maxHeight: 2000, maxPixels: 3000);

        expect($config->maxWidth())->toBe(1000)
            ->and($config->maxHeight())->toBe(2000)
            ->and($config->maxPixels())->toBe(3000)
            ->and($config->memoryBytes())->toBe(268_435_456)
            ->and($config->timeSeconds())->toBe(60);
    });

    it('ships safe default limits including a 50 megapixel area cap', function (): void {
        $defaults = require dirname(__DIR__, 2) . '/config/media-imagick.php';

        expect($defaults['limits']['max_pixels'])->toBe(50_000_000)
            ->and($defaults['limits']['max_width'])->toBeGreaterThan(0)
            ->and($defaults['limits']['max_height'])->toBeGreaterThan(0)
            ->and($defaults['limits']['memory_bytes'])->toBeGreaterThan(0)
            ->and($defaults['limits']['time_seconds'])->toBeGreaterThan(0);
    });
});
