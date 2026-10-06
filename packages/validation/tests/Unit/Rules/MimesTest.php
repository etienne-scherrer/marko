<?php

declare(strict_types=1);

use Marko\Validation\Rules\Mimes;
use Marko\Validation\Tests\Support\TestUploads;

it('passes when the sniffed extension is listed', function (): void {
    $rule = new Mimes('jpg', 'png', 'pdf');

    expect($rule->passes('upload', TestUploads::png(), []))->toBeTrue()
        ->and($rule->passes('upload', TestUploads::pdf(), []))->toBeTrue();
});

it('fails when the sniffed extension is not listed', function (): void {
    $rule = new Mimes('jpg', 'png');
    $pdf = TestUploads::pdf();

    expect($rule->passes('upload', $pdf, []))->toBeFalse()
        ->and($rule->message('upload', $pdf))->toBe('The upload field must be a file of type: jpg, png.');
});

it('ignores a lying client filename and media type', function (): void {
    $liar = TestUploads::text(clientFilename: 'photo.png', clientMediaType: 'image/png');

    expect(new Mimes('png')->passes('upload', $liar, []))->toBeFalse()
        ->and(new Mimes('txt')->passes('upload', $liar, []))->toBeTrue();
});

it('treats jpeg and jpg as the same extension', function (): void {
    expect(new Mimes('jpeg')->passes('upload', TestUploads::jpeg(), []))->toBeTrue()
        ->and(new Mimes('JPG')->passes('upload', TestUploads::jpeg(), []))->toBeTrue()
        ->and(new Mimes('jpeg')->passes('upload', TestUploads::png(), []))->toBeFalse();
});

it('allows svg only when listed explicitly', function (): void {
    expect(new Mimes('png')->passes('upload', TestUploads::svg(), []))->toBeFalse()
        ->and(new Mimes('svg')->passes('upload', TestUploads::svg(), []))->toBeTrue();
});

it('fails for a non-file and for an upload error', function (): void {
    $rule = new Mimes('png');

    expect($rule->passes('upload', 'photo.png', []))->toBeFalse()
        ->and($rule->passes('upload', TestUploads::failed(), []))->toBeFalse()
        ->and($rule->message('upload', 'photo.png'))->toBe('The upload field must be a file.');
});

it('throws when no extensions are given', function (): void {
    new Mimes();
})->throws(InvalidArgumentException::class, 'The mimes rule needs at least one extension');
