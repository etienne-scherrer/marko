<?php

declare(strict_types=1);

use Marko\Validation\Contracts\RuleInterface;
use Marko\Validation\Rules\Max;
use Marko\Validation\Tests\Support\TestUploads;

it('fails max for an uploaded file with a message pointing to max_size', function (): void {
    $rule = new Max(2048);
    $file = TestUploads::sized(10);

    expect($rule->passes('avatar', $file, []))->toBeFalse()
        ->and($rule->message('avatar', $file))
        ->toBe('The avatar field is a file: use max_size:2048 to limit its size in kilobytes.');
});

it('still counts items for an array of uploaded files', function (): void {
    $rule = new Max(2);

    expect($rule->passes('photos', [TestUploads::png(), TestUploads::gif()], []))->toBeTrue()
        ->and($rule->passes('photos', [TestUploads::png(), TestUploads::gif(), TestUploads::jpeg()], []))->toBeFalse();
});

it('implements RuleInterface', function () {
    expect(new Max(5))->toBeInstanceOf(RuleInterface::class);
});

it('passes for string at maximum length', function () {
    $rule = new Max(5);

    expect($rule->passes('name', 'abcde', []))->toBeTrue();
});

it('passes for string below maximum length', function () {
    $rule = new Max(5);

    expect($rule->passes('name', 'abc', []))->toBeTrue();
});

it('fails for string exceeding maximum length', function () {
    $rule = new Max(5);

    expect($rule->passes('name', 'abcdefg', []))->toBeFalse();
});

it('passes for number at maximum', function () {
    $rule = new Max(100);

    expect($rule->passes('age', 100, []))->toBeTrue();
});

it('passes for number below maximum', function () {
    $rule = new Max(100);

    expect($rule->passes('age', 50, []))->toBeTrue();
});

it('fails for number exceeding maximum', function () {
    $rule = new Max(100);

    expect($rule->passes('age', 150, []))->toBeFalse();
});

it('passes for array with maximum items', function () {
    $rule = new Max(3);

    expect($rule->passes('items', ['a', 'b', 'c'], []))->toBeTrue();
});

it('fails for array exceeding maximum items', function () {
    $rule = new Max(3);

    expect($rule->passes('items', ['a', 'b', 'c', 'd'], []))->toBeFalse();
});

it('passes for null value', function () {
    $rule = new Max(5);

    expect($rule->passes('name', null, []))->toBeTrue();
});

it('passes for empty string', function () {
    $rule = new Max(5);

    expect($rule->passes('name', '', []))->toBeTrue();
});

it('handles multibyte strings correctly', function () {
    $rule = new Max(3);

    expect($rule->passes('name', '日本語', []))->toBeTrue()
        ->and($rule->passes('name', '日本語テスト', []))->toBeFalse();
});

it('returns correct message for string', function () {
    $rule = new Max(5);

    expect($rule->message('name', 'abcdefg'))->toBe('The name field must not exceed 5 characters.');
});

it('returns correct message for array', function () {
    $rule = new Max(3);

    expect($rule->message('items', ['a', 'b', 'c', 'd']))->toBe('The items field must not have more than 3 items.');
});

it('returns correct message for number', function () {
    $rule = new Max(100);

    expect($rule->message('age', 150))->toBe('The age field must not exceed 100.');
});

it('compares numerically for the max rule on a numeric string', function () {
    $rule = new Max(100);

    expect($rule->passes('age', '50', []))->toBeTrue()
        ->and($rule->passes('age', '150', []))->toBeFalse();
});
