<?php

declare(strict_types=1);

namespace Marko\MediaImagick\Config;

use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigNotFoundException;

readonly class ImagickConfig
{
    public function __construct(
        private ConfigRepositoryInterface $config,
    ) {}

    /**
     * @return array<string>
     * @throws ConfigNotFoundException
     */
    public function allowedRasterFormats(): array
    {
        return array_map(
            static fn (mixed $format): string => strtoupper((string) $format),
            $this->config->getArray('media-imagick.allowed_raster_formats'),
        );
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function maxWidth(): int
    {
        return $this->config->getInt('media-imagick.limits.max_width');
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function maxHeight(): int
    {
        return $this->config->getInt('media-imagick.limits.max_height');
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function maxPixels(): int
    {
        return $this->config->getInt('media-imagick.limits.max_pixels');
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function memoryBytes(): int
    {
        return $this->config->getInt('media-imagick.limits.memory_bytes');
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function timeSeconds(): int
    {
        return $this->config->getInt('media-imagick.limits.time_seconds');
    }
}
