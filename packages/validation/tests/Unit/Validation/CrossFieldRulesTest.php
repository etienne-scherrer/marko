<?php

declare(strict_types=1);

use Marko\Validation\Contracts\WildcardAwareRuleInterface;
use Marko\Validation\Rules\Different;
use Marko\Validation\Rules\Same;
use Marko\Validation\Validation\Validator;

it('resolves confirmed against a nested dot path', function (): void {
    $validator = new Validator();
    $rules = ['account.password' => 'confirmed'];

    expect($validator->passes(
        ['account' => ['password' => 'secret', 'password_confirmation' => 'secret']],
        $rules,
    ))->toBeTrue()
        ->and($validator->validate(
            ['account' => ['password' => 'secret', 'password_confirmation' => 'other']],
            $rules,
        )->all())->toBe(['account.password' => ['The account.password confirmation does not match.']]);
});

it('resolves confirmed inside a wildcard row', function (): void {
    $errors = new Validator()->validate(
        ['users' => [
            ['pin' => '1234', 'pin_confirmation' => '1234'],
            ['pin' => '5678', 'pin_confirmation' => '0000'],
        ]],
        ['users.*.pin' => 'confirmed'],
    );

    expect($errors->all())->toBe(['users.1.pin' => ['The users.1.pin confirmation does not match.']]);
});

it('resolves same and different against absolute dot paths', function (): void {
    $data = ['billing' => ['email' => 'a@example.com'], 'shipping' => ['email' => 'a@example.com']];
    $validator = new Validator();

    expect($validator->passes($data, ['shipping.email' => 'same:billing.email']))->toBeTrue()
        ->and($validator->validate($data, ['shipping.email' => 'different:billing.email'])->all())
        ->toBe(['shipping.email' => ['The shipping.email field must be different from the billing.email field.']])
        ->and($validator->passes(['a' => 'x', 'b' => 'x'], ['a' => 'same:b']))->toBeTrue();
});

it('replaces the wildcard in same with the current row index', function (): void {
    $errors = new Validator()->validate(
        ['items' => [
            ['sku' => 'A1', 'sku_check' => 'A1'],
            ['sku' => 'B2', 'sku_check' => 'X9'],
        ]],
        ['items.*.sku_check' => 'same:items.*.sku'],
    );

    expect($errors->all())->toBe([
        'items.1.sku_check' => ['The items.1.sku_check field must match the items.1.sku field.'],
    ]);
});

it('replaces the wildcard in different with the current row index', function (): void {
    $errors = new Validator()->validate(
        ['matrix' => [
            [['from' => 1, 'to' => 2]],
            [['from' => 3, 'to' => 3]],
        ]],
        ['matrix.*.*.to' => 'different:matrix.*.*.from'],
    );

    expect($errors->all())->toBe([
        'matrix.1.0.to' => ['The matrix.1.0.to field must be different from the matrix.1.0.from field.'],
    ]);
});

it('throws when the other field has more wildcards than the rule key', function (): void {
    new Validator()->validate(
        ['items' => [['sku' => 'A']], 'code' => 'A'],
        ['code' => 'same:items.*.sku'],
    );
})->throws(
    InvalidArgumentException::class,
    'The same:items.*.sku rule has more wildcards than the code key it validates',
);

it('throws for a misconfigured cross-field wildcard even when there is no data', function (): void {
    new Validator()->validate([], ['items.*.code' => 'different:groups.*.items.*.code']);
})->throws(
    InvalidArgumentException::class,
    'The different:groups.*.items.*.code rule has more wildcards than the items.*.code key it validates',
);

it('lets same and different rewrite their wildcards for a row', function (): void {
    expect(new Same('items.*.sku'))->toBeInstanceOf(WildcardAwareRuleInterface::class)
        ->and(new Different('items.*.sku'))->toBeInstanceOf(WildcardAwareRuleInterface::class)
        ->and(new Same('items.*.sku')->forWildcardIndexes('items.*.sku_check', ['3'])->message('x', null))
        ->toBe('The x field must match the items.3.sku field.');
});
