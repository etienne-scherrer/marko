<?php

declare(strict_types=1);

use Marko\Validation\Rules\MimeTypes;
use Marko\Validation\Tests\Support\TestUploads;

it('passes when the sniffed MIME type is listed', function (): void {
    expect(new MimeTypes('application/pdf', 'image/png')->passes('upload', TestUploads::pdf(), []))->toBeTrue();
});

it('fails when the sniffed MIME type is not listed', function (): void {
    $rule = new MimeTypes('application/pdf');
    $png = TestUploads::png();

    expect($rule->passes('upload', $png, []))->toBeFalse()
        ->and($rule->message('upload', $png))->toBe('The upload field must be a file of type: application/pdf.');
});

it('matches type wildcards', function (): void {
    $rule = new MimeTypes('image/*');

    expect($rule->passes('upload', TestUploads::png(), []))->toBeTrue()
        ->and($rule->passes('upload', TestUploads::gif(), []))->toBeTrue()
        ->and($rule->passes('upload', TestUploads::pdf(), []))->toBeFalse();
});

it('matches types case-insensitively', function (): void {
    expect(new MimeTypes('IMAGE/PNG')->passes('upload', TestUploads::png(), []))->toBeTrue()
        ->and(new MimeTypes('Image/*')->passes('upload', TestUploads::png(), []))->toBeTrue();
});

it('ignores a lying client filename and media type', function (): void {
    $liar = TestUploads::text(clientFilename: 'invoice.pdf', clientMediaType: 'application/pdf');

    expect(new MimeTypes('application/pdf')->passes('upload', $liar, []))->toBeFalse()
        ->and(new MimeTypes('text/plain')->passes('upload', $liar, []))->toBeTrue();
});

it('fails for a non-file and for an upload error', function (): void {
    $rule = new MimeTypes('image/*');

    expect($rule->passes('upload', 'image/png', []))->toBeFalse()
        ->and($rule->passes('upload', TestUploads::failed(), []))->toBeFalse();
});

it('throws when no types are given', function (): void {
    new MimeTypes();
})->throws(InvalidArgumentException::class, 'The mimetypes rule needs at least one MIME type');
