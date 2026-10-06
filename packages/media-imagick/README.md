# marko/media-imagick

ImageMagick image processing for marko/media — resize, crop, and convert images with superior quality, AVIF support, and ICC color profile handling.

## Installation

```bash
composer require marko/media-imagick
```

> **Requirement:** The Imagick PHP extension must be installed (`pecl install imagick`).

## Quick Example

```php
use Marko\Media\Contracts\ImageProcessorInterface;

// Inject ImageProcessorInterface; this package binds it to ImagickImageProcessor.
$outputPath = $imageProcessor->resize(
    imagePath: '/path/to/image.jpg',
    width: 800,
    height: 600,
);
```

## Documentation

Full usage, security model (input format sniffing, resource limits, recommended `policy.xml`), API reference, and examples: [marko/media-imagick](https://marko.build/docs/packages/media-imagick/)
