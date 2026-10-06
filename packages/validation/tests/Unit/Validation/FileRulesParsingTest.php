<?php

declare(strict_types=1);

use Marko\Validation\Rules\File;
use Marko\Validation\Rules\Image;
use Marko\Validation\Rules\MaxSize;
use Marko\Validation\Rules\Mimes;
use Marko\Validation\Rules\MimeTypes;
use Marko\Validation\Rules\MinSize;
use Marko\Validation\Tests\Support\TestUploads;
use Marko\Validation\Validation\RuleParser;
use Marko\Validation\Validation\Validator;

it('parses each file rule name', function (): void {
    $rules = new RuleParser()->parse('file|image|mimes:jpg,png|mimetypes:image/*|max_size:2048|min_size:1');

    expect($rules)->toHaveCount(6)
        ->and($rules[0])->toBeInstanceOf(File::class)
        ->and($rules[1])->toBeInstanceOf(Image::class)
        ->and($rules[2])->toBeInstanceOf(Mimes::class)
        ->and($rules[3])->toBeInstanceOf(MimeTypes::class)
        ->and($rules[4])->toBeInstanceOf(MaxSize::class)
        ->and($rules[5])->toBeInstanceOf(MinSize::class);
});

it('throws when max_size or min_size has no numeric parameter', function (string $rule): void {
    new RuleParser()->parse($rule);
})->with(['max_size', 'max_size:', 'min_size:big'])
    ->throws(InvalidArgumentException::class, 'needs a size in kilobytes');

it('throws when mimes or mimetypes has no entries', function (string $rule): void {
    new RuleParser()->parse($rule);
})->with(['mimes', 'mimes:', 'mimetypes: , '])
    ->throws(InvalidArgumentException::class, 'needs at least one');

it('validates an uploaded file with string rules', function (): void {
    $errors = new Validator()->validate(
        ['avatar' => TestUploads::png()],
        ['avatar' => 'required|file|image|mimes:png,jpg|mimetypes:image/*|max_size:2048|min_size:0'],
    );

    expect($errors->isEmpty())->toBeTrue();
});

it('reports a too-large file under its field', function (): void {
    $errors = new Validator()->validate(
        ['avatar' => TestUploads::sized(4096)],
        ['avatar' => 'required|file|max_size:2'],
    );

    expect($errors->all())->toBe(['avatar' => ['The avatar field must not be larger than 2 kilobytes.']]);
});

it('skips an absent optional file and reports a missing required file', function (): void {
    $validator = new Validator();

    expect($validator->validate([], ['avatar' => 'nullable|file|image'])->isEmpty())->toBeTrue()
        ->and($validator->validate([], ['avatar' => 'required|file'])->all())
        ->toBe(['avatar' => ['The avatar field is required.', 'The avatar field must be a file.']]);
});
