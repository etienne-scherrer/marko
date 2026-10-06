<?php

declare(strict_types=1);

use Marko\Validation\Contracts\RuleInterface;
use Marko\Validation\Rules\File;
use Marko\Validation\Tests\Support\TestUploads;

it('implements RuleInterface', function (): void {
    expect(new File())->toBeInstanceOf(RuleInterface::class);
});

it('passes for a valid uploaded file', function (): void {
    expect(new File()->passes('document', TestUploads::text(), []))->toBeTrue();
});

it('fails for a value that is not a file', function (): void {
    $rule = new File();

    expect($rule->passes('document', 'notes.txt', []))->toBeFalse()
        ->and($rule->passes('document', ['notes.txt'], []))->toBeFalse()
        ->and($rule->message('document', 'notes.txt'))->toBe('The document field must be a file.');
});

it('fails for a file with an upload error', function (): void {
    $rule = new File();
    $file = TestUploads::failed(UPLOAD_ERR_PARTIAL);

    expect($rule->passes('document', $file, []))->toBeFalse()
        ->and($rule->message('document', $file))->toBe('The document file failed to upload.');
});

it('explains that a file rejected for exceeding the server limit is too large', function (): void {
    $file = TestUploads::failed(UPLOAD_ERR_INI_SIZE);

    expect(new File()->message('document', $file))
        ->toBe('The document file is larger than the server allows.');
});

it('fails for a file that was already moved', function (): void {
    $file = TestUploads::text();
    $file->moveTo(sys_get_temp_dir() . '/marko-validation-moved-' . bin2hex(random_bytes(6)));
    $rule = new File();

    expect($rule->passes('document', $file, []))->toBeFalse()
        ->and($rule->message('document', $file))->toBe('The document file has already been moved.');
});
