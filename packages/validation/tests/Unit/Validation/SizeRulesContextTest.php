<?php

declare(strict_types=1);

use Marko\Validation\Rules\Integer;
use Marko\Validation\Rules\Max;
use Marko\Validation\Rules\Min;
use Marko\Validation\Rules\Numeric;
use Marko\Validation\Validation\Validator;

it('rejects a one-digit password under string|min:8', function (): void {
    $errors = new Validator()->validate(
        ['password' => '9'],
        ['password' => 'required|string|min:8'],
    );

    expect($errors->get('password'))->toBe(['The password field must be at least 8 characters.']);
});

it('rejects a 300-character digit string under string|max:255', function (): void {
    $errors = new Validator()->validate(
        ['bio' => str_repeat('0', 300)],
        ['bio' => 'required|string|max:255'],
    );

    expect($errors->get('bio'))->toBe(['The bio field must not exceed 255 characters.']);
});

it('measures a numeric string by length when the field has no numeric or integer rule', function (): void {
    $validator = new Validator();

    expect($validator->passes(['code' => '9'], ['code' => 'min:8']))->toBeFalse()
        ->and($validator->passes(['code' => '12345678'], ['code' => 'min:8']))->toBeTrue();
});

it('compares a numeric string by value under numeric|min:8', function (): void {
    $validator = new Validator();

    expect($validator->passes(['qty' => '9'], ['qty' => 'numeric|min:8']))->toBeTrue()
        ->and($validator->validate(['qty' => '7'], ['qty' => 'numeric|min:8'])->get('qty'))
        ->toBe(['The qty field must be at least 8.']);
});

it('compares a numeric string by value under integer|between:1,10', function (): void {
    $validator = new Validator();

    expect($validator->passes(['page' => '10'], ['page' => 'integer|between:1,10']))->toBeTrue()
        ->and($validator->passes(['page' => '1'], ['page' => 'integer|between:1,10']))->toBeTrue()
        ->and($validator->passes(['page' => '11'], ['page' => 'integer|between:1,10']))->toBeFalse()
        ->and($validator->passes(['page' => '0'], ['page' => 'integer|between:1,10']))->toBeFalse();
});

it('measures " 1e3" by length under string|max:3', function (): void {
    $errors = new Validator()->validate(
        ['code' => ' 1e3'],
        ['code' => 'string|max:3'],
    );

    expect($errors->get('code'))->toBe(['The code field must not exceed 3 characters.']);
});

it('compares actual ints and floats by value without a numeric rule', function (): void {
    $validator = new Validator();

    expect($validator->passes(['age' => 9], ['age' => 'min:8']))->toBeTrue()
        ->and($validator->passes(['age' => 7.5], ['age' => 'min:8']))->toBeFalse();
});

it('applies numeric mode when numeric and size rules are given as rule objects', function (): void {
    $validator = new Validator();

    expect($validator->passes(['qty' => '9'], ['qty' => [new Numeric(), new Min(8)]]))->toBeTrue()
        ->and($validator->passes(['qty' => '150'], ['qty' => [new Integer(), new Max(100)]]))->toBeFalse()
        ->and($validator->passes(['qty' => '9'], ['qty' => [new Min(8)]]))->toBeFalse();
});

it('applies numeric mode across mixed string and array rule definitions', function (): void {
    $validator = new Validator();

    expect($validator->passes(['qty' => '9'], ['qty' => ['numeric', 'min:8']]))->toBeTrue()
        ->and($validator->passes(['qty' => '9'], ['qty' => [new Numeric(), 'min:8']]))->toBeTrue();
});

it('applies numeric mode per wildcard field', function (): void {
    $errors = new Validator()->validate(
        ['items' => [['qty' => '5'], ['qty' => '0']], 'tags' => ['9']],
        ['items.*.qty' => 'integer|min:1', 'tags.*' => 'string|min:2'],
    );

    expect($errors->has('items.0.qty'))->toBeFalse()
        ->and($errors->has('items.1.qty'))->toBeTrue()
        ->and($errors->has('tags.0'))->toBeTrue();
});
