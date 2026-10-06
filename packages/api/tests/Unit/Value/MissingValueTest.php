<?php

declare(strict_types=1);

use Marko\Api\Exceptions\ApiResourceException;
use Marko\Api\Value\MissingValue;

it('provides MissingValue sentinel class for marking fields to be omitted', function () {
    $missing = new MissingValue();

    expect($missing)->toBeInstanceOf(MissingValue::class);
});

it('is instantiable with no arguments', function () {
    $reflection = new ReflectionClass(MissingValue::class);

    expect($reflection->getConstructor())->toBeNull();
});

it('is a class with no properties', function () {
    $reflection = new ReflectionClass(MissingValue::class);

    expect($reflection->getProperties())->toBeEmpty();
});

it('throws instead of serializing when passed to json_encode', function () {
    expect(fn () => json_encode(['hash' => new MissingValue()], JSON_THROW_ON_ERROR))
        ->toThrow(ApiResourceException::class, 'MissingValue');
});

it('implements JsonSerializable so it cannot be silently serialized', function () {
    expect(new MissingValue())->toBeInstanceOf(JsonSerializable::class);
});
