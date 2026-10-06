<?php

declare(strict_types=1);

use Marko\Validation\Tests\Support\TestUploads;
use Marko\Validation\Validation\DataPath;
use Marko\Validation\Validation\Validator;

describe('wildcard keys', function (): void {
    it('validates every item of photos.* and keys errors by index', function (): void {
        $errors = new Validator()->validate(
            ['photos' => [TestUploads::png(), TestUploads::pdf(), TestUploads::sized(2048)]],
            ['photos.*' => 'image|max_size:1'],
        );

        expect($errors->all())->toBe([
            'photos.1' => ['The photos.1 field must be an image (JPEG, PNG, GIF or WebP).'],
            'photos.2' => [
                'The photos.2 field must be an image (JPEG, PNG, GIF or WebP).',
                'The photos.2 field must not be larger than 1 kilobytes.',
            ],
        ]);
    });

    it('no longer silently passes invalid items under a wildcard rule', function (): void {
        $validator = new Validator();

        expect($validator->passes(['photos' => [TestUploads::pdf()]], ['photos.*' => 'image']))->toBeFalse()
            ->and($validator->passes(['tags' => ['php', 42]], ['tags.*' => 'string']))->toBeFalse()
            ->and(
                $validator->passes(['photos' => [TestUploads::png()]], ['photos.*' => 'required|image']),
            )->toBeTrue();
    });

    it('expands a nested items.*.name key', function (): void {
        $errors = new Validator()->validate(
            ['items' => [['name' => 'Desk'], ['name' => ''], ['sku' => 'X1']]],
            ['items.*.name' => 'required|string'],
        );

        expect($errors->all())->toBe([
            'items.1.name' => ['The items.1.name field is required.'],
            'items.2.name' => ['The items.2.name field is required.'],
        ]);
    });

    it('expands multiple wildcards in matrix.*.*', function (): void {
        $errors = new Validator()->validate(
            ['matrix' => [[1, 'a'], ['row' => 3, 'col' => 'b']]],
            ['matrix.*.*' => 'integer'],
        );

        expect(array_keys($errors->all()))->toBe(['matrix.0.1', 'matrix.1.col']);
    });

    it('produces no errors for a wildcard over an absent or empty parent', function (mixed $photos): void {
        $data = $photos === 'absent' ? [] : ['photos' => $photos];

        expect(new Validator()->validate($data, ['photos.*' => 'required|image'])->isEmpty())->toBeTrue();
    })->with(['absent', 'null' => [null], 'empty array' => [[]], 'empty string' => ['']]);

    it('still fails a required array parent when it is absent', function (): void {
        $errors = new Validator()->validate([], [
            'photos' => 'required|array',
            'photos.*' => 'required|image',
        ]);

        expect($errors->all())->toBe([
            'photos' => ['The photos field is required.'],
        ]);
    });

    it('fails loudly when a wildcard segment meets a scalar', function (): void {
        $errors = new Validator()->validate(
            ['photos' => 'not-a-list', 'matrix' => [[1], 'flat']],
            ['photos.*' => 'image', 'matrix.*.*' => 'integer'],
        );

        expect($errors->all())->toBe([
            'photos' => ['The photos field must be an array to apply the photos.* rules.'],
            'matrix.1' => ['The matrix.1 field must be an array to apply the matrix.*.* rules.'],
        ]);
    });

    it('reports a scalar at a wildcard position under every key that reaches it', function (): void {
        $errors = new Validator()->validate(
            ['items' => 'flat'],
            ['items.*' => 'array', 'items.*.name' => 'string'],
        );

        expect($errors->get('items'))->toBe([
            'The items field must be an array to apply the items.* rules.',
            'The items field must be an array to apply the items.*.name rules.',
        ]);
    });

    it('fails loudly when a wildcard segment meets a single uploaded file', function (): void {
        $errors = new Validator()->validate(['photos' => TestUploads::png()], ['photos.*' => 'image']);

        expect($errors->all())->toBe([
            'photos' => ['The photos field must be an array to apply the photos.* rules.'],
        ]);
    });

    it('throws for an unknown rule under a wildcard key even when the parent is empty', function (): void {
        new Validator()->validate(['photos' => []], ['photos.*' => 'imagee']);
    })->throws(InvalidArgumentException::class, 'Unknown validation rule: imagee');

    it('treats a leading wildcard as every top-level key', function (): void {
        $errors = new Validator()->validate(['a' => 'x', 'b' => 5], ['*' => 'string']);

        expect(array_keys($errors->all()))->toBe(['b']);
    });
});

describe('DataPath', function (): void {
    it('resolves dot paths with DataPath', function (): void {
        $data = ['user' => ['address' => ['city' => 'Rome']], 'items' => [['sku' => 'A']], 'flat' => 'x'];

        expect(DataPath::get($data, 'user.address.city'))->toBe('Rome')
            ->and(DataPath::get($data, 'items.0.sku'))->toBe('A')
            ->and(DataPath::get($data, 'flat'))->toBe('x')
            ->and(DataPath::get($data, 'flat.deeper'))->toBeNull()
            ->and(DataPath::get($data, 'user.missing'))->toBeNull()
            ->and(DataPath::get($data, 'missing'))->toBeNull();
    });
});
