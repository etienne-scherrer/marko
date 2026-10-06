<?php

declare(strict_types=1);

use Marko\Validation\Contracts\RuleInterface;
use Marko\Validation\Rules\Min;
use Marko\Validation\Tests\Support\TestUploads;

it('fails min for an uploaded file with a message pointing to min_size', function (): void {
    $rule = new Min(1);
    $file = TestUploads::sized(4096);

    expect($rule->passes('avatar', $file, []))->toBeFalse()
        ->and($rule->message('avatar', $file))
        ->toBe('The avatar field is a file: use min_size:1 to require a minimum size in kilobytes.');
});

it('implements RuleInterface', function () {
    expect(new Min(3))->toBeInstanceOf(RuleInterface::class);
});

it('passes for string with exact minimum length', function () {
    $rule = new Min(3);

    expect($rule->passes('name', 'abc', []))->toBeTrue();
});

it('passes for string exceeding minimum length', function () {
    $rule = new Min(3);

    expect($rule->passes('name', 'abcdef', []))->toBeTrue();
});

it('fails for string below minimum length', function () {
    $rule = new Min(3);

    expect($rule->passes('name', 'ab', []))->toBeFalse();
});

it('passes for number at minimum', function () {
    $rule = new Min(5);

    expect($rule->passes('age', 5, []))->toBeTrue();
});

it('passes for number above minimum', function () {
    $rule = new Min(5);

    expect($rule->passes('age', 10, []))->toBeTrue();
});

it('fails for number below minimum', function () {
    $rule = new Min(5);

    expect($rule->passes('age', 3, []))->toBeFalse();
});

it('passes for array with minimum items', function () {
    $rule = new Min(2);

    expect($rule->passes('items', ['a', 'b'], []))->toBeTrue();
});

it('fails for array with fewer than minimum items', function () {
    $rule = new Min(2);

    expect($rule->passes('items', ['a'], []))->toBeFalse();
});

it('passes for null value', function () {
    $rule = new Min(3);

    expect($rule->passes('name', null, []))->toBeTrue();
});

it('passes for empty string', function () {
    $rule = new Min(3);

    expect($rule->passes('name', '', []))->toBeTrue();
});

it('handles multibyte strings correctly', function () {
    $rule = new Min(3);

    expect($rule->passes('name', '日本語', []))->toBeTrue()
        ->and($rule->passes('name', '日本', []))->toBeFalse();
});

it('returns correct message for string', function () {
    $rule = new Min(3);

    expect($rule->message('name', 'ab'))->toBe('The name field must be at least 3 characters.');
});

it('returns correct message for array', function () {
    $rule = new Min(3);

    expect($rule->message('items', ['a']))->toBe('The items field must have at least 3 items.');
});

it('returns correct message for number', function () {
    $rule = new Min(10);

    expect($rule->message('age', 5))->toBe('The age field must be at least 10.');
});

it('accepts a numeric string at or above the minimum in numeric mode', function () {
    $rule = new Min(18, numeric: true);

    expect($rule->passes('age', '25', []))->toBeTrue()
        ->and($rule->passes('age', '18', []))->toBeTrue();
});

it('rejects a numeric string below the minimum in numeric mode', function () {
    $rule = new Min(18, numeric: true);

    expect($rule->passes('age', '5', []))->toBeFalse();
});

it('still measures string length for the min rule on a non-numeric string', function () {
    $rule = new Min(3);

    expect($rule->passes('name', 'abc', []))->toBeTrue()
        ->and($rule->passes('name', 'ab', []))->toBeFalse();
});

it('counts array items for the min and between rules on an array value', function () {
    $minRule = new Min(2);

    expect($minRule->passes('items', ['a', 'b'], []))->toBeTrue()
        ->and($minRule->passes('items', ['a'], []))->toBeFalse();
});

it(
    'produces a value-oriented (not character-length) failure message for a numeric string failing min in numeric mode',
    function () {
        $rule = new Min(18, numeric: true);
    
        expect($rule->message('age', '5'))->toBe('The age field must be at least 18.');
    }
);

it('measures a numeric string by length when not in numeric mode', function (): void {
    $rule = new Min(8);

    expect($rule->passes('password', '9', []))->toBeFalse()
        ->and($rule->passes('password', '12345678', []))->toBeTrue()
        ->and($rule->message('password', '9'))->toBe('The password field must be at least 8 characters.');
});

it('returns a numeric-mode copy from asNumeric', function (): void {
    $rule = new Min(8);

    expect($rule->asNumeric()->passes('age', '9', []))->toBeTrue()
        ->and($rule->passes('age', '9', []))->toBeFalse();
});
