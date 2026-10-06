<?php

declare(strict_types=1);

use Marko\Validation\Rules\Image;
use Marko\Validation\Tests\Support\TestUploads;

it('passes image for a png', function (): void {
    expect(new Image()->passes('avatar', TestUploads::png(), []))->toBeTrue();
});

it('passes image for a jpeg', function (): void {
    expect(new Image()->passes('avatar', TestUploads::jpeg(), []))->toBeTrue();
});

it('passes image for a gif', function (): void {
    expect(new Image()->passes('avatar', TestUploads::gif(), []))->toBeTrue();
});

it('fails image for a text file named and typed as png', function (): void {
    $liar = TestUploads::text(clientFilename: 'avatar.png', clientMediaType: 'image/png');
    $rule = new Image();

    expect($rule->passes('avatar', $liar, []))->toBeFalse()
        ->and($rule->message('avatar', $liar))->toBe('The avatar field must be an image (JPEG, PNG, GIF or WebP).');
});

it('fails image for an svg', function (): void {
    expect(new Image()->passes('avatar', TestUploads::svg(), []))->toBeFalse();
});

it('fails image for a pdf', function (): void {
    expect(new Image()->passes('avatar', TestUploads::pdf(), []))->toBeFalse();
});

it('fails image for a value that is not a file', function (): void {
    $rule = new Image();

    expect($rule->passes('avatar', 'avatar.png', []))->toBeFalse()
        ->and($rule->message('avatar', 'avatar.png'))->toBe('The avatar field must be a file.');
});

it('fails image for a file with an upload error without reading it', function (): void {
    $file = TestUploads::failed(UPLOAD_ERR_PARTIAL);
    $rule = new Image();

    expect($rule->passes('avatar', $file, []))->toBeFalse()
        ->and($rule->message('avatar', $file))->toBe('The avatar file failed to upload.');
});
