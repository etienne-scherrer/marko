<?php

declare(strict_types=1);

use Marko\Validation\Rules\MaxSize;
use Marko\Validation\Rules\MinSize;
use Marko\Validation\Tests\Support\TestUploads;

describe('max_size', function (): void {
    it('passes for a file at the maximum size', function (): void {
        expect(new MaxSize(2)->passes('upload', TestUploads::sized(2048), []))->toBeTrue();
    });

    it('fails for a file over the maximum size', function (): void {
        $rule = new MaxSize(2);
        $file = TestUploads::sized(2049);

        expect($rule->passes('upload', $file, []))->toBeFalse()
            ->and($rule->message('upload', $file))->toBe('The upload field must not be larger than 2 kilobytes.');
    });

    it('accepts fractional kilobytes', function (): void {
        $rule = new MaxSize(0.5);

        expect($rule->passes('upload', TestUploads::sized(512), []))->toBeTrue()
            ->and($rule->passes('upload', TestUploads::sized(513), []))->toBeFalse();
    });

    it('fails for a non-file value', function (): void {
        $rule = new MaxSize(2048);

        expect($rule->passes('upload', 100, []))->toBeFalse()
            ->and($rule->message('upload', 100))->toBe('The upload field must be a file.');
    });

    it('fails for a file with an upload error', function (): void {
        expect(new MaxSize(2048)->passes('upload', TestUploads::failed(), []))->toBeFalse();
    });
});

describe('min_size', function (): void {
    it('passes for a file at the minimum size', function (): void {
        expect(new MinSize(1)->passes('upload', TestUploads::sized(1024), []))->toBeTrue();
    });

    it('fails for a file under the minimum size', function (): void {
        $rule = new MinSize(1);
        $file = TestUploads::sized(1023);

        expect($rule->passes('upload', $file, []))->toBeFalse()
            ->and($rule->message('upload', $file))->toBe('The upload field must be at least 1 kilobytes.');
    });

    it('fails for a non-file value', function (): void {
        expect(new MinSize(1)->passes('upload', 'abc', []))->toBeFalse();
    });

    it('fails for a file with an upload error', function (): void {
        expect(new MinSize(1)->passes('upload', TestUploads::failed(), []))->toBeFalse();
    });
});
