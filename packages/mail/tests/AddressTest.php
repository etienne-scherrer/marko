<?php

declare(strict_types=1);

use Marko\Mail\Address;
use Marko\Mail\Exceptions\MessageException;

describe('Address', function (): void {
    it('stores email correctly', function (): void {
        $address = new Address('john@example.com');

        expect($address->email)->toBe('john@example.com');
    });

    it('stores optional name correctly', function (): void {
        $address = new Address('john@example.com', 'John Doe');

        expect($address->name)->toBe('John Doe');
    });

    it('throws MessageException for invalid email', function (): void {
        expect(fn () => new Address('not-an-email'))
            ->toThrow(MessageException::class, "Invalid email address: 'not-an-email'");
    });

    it('toString formats with name correctly', function (): void {
        $address = new Address('john@example.com', 'John Doe');

        expect($address->toString())->toBe('John Doe <john@example.com>');
    });

    it('toString formats without name correctly', function (): void {
        $address = new Address('john@example.com');

        expect($address->toString())->toBe('john@example.com');
    });

    it('is readonly', function (): void {
        $reflection = new ReflectionClass(Address::class);

        expect($reflection->isReadOnly())->toBeTrue();
    });

    it('rejects various invalid email formats', function (string $invalidEmail): void {
        expect(fn () => new Address($invalidEmail))
            ->toThrow(MessageException::class);
    })->with([
        'missing at symbol' => ['not-an-email'],
        'missing local part' => ['@missing-local.com'],
        'missing domain' => ['missing-domain@'],
        'spaces in local part' => ['spaces in@email.com'],
        'empty string' => [''],
        'multiple at symbols' => ['user@@domain.com'],
        'trailing dot in domain' => ['user@domain.'],
        'leading dot in domain' => ['user@.domain.com'],
        'double dots in domain' => ['user@domain..com'],
        'special characters without quotes' => ['user<>@domain.com'],
    ]);

    it('rejects a carriage return in the Address display name', function (): void {
        expect(fn () => new Address('user@example.com', "John\rDoe"))
            ->toThrow(MessageException::class);
    });

    it('rejects a line feed in the Address display name', function (): void {
        expect(fn () => new Address('user@example.com', "John\nDoe"))
            ->toThrow(MessageException::class);
    });

    it('still constructs an Address with a legitimate display name', function (): void {
        $address = new Address('user@example.com', 'John Doe');

        expect($address->name)->toBe('John Doe')
            ->and($address->email)->toBe('user@example.com');
    });

    it('handles edge cases in names', function (string $name, string $expected): void {
        $address = new Address('user@example.com', $name);

        expect($address->name)->toBe($name)
            ->and($address->toString())->toBe($expected);
    })->with([
        'name with double quotes' => ['"John Doe"', '"\\"John Doe\\"" <user@example.com>'],
        'name with single quotes' => ["O'Brien", "O'Brien <user@example.com>"],
        'name with special characters' => ['John & Jane Doe!', 'John & Jane Doe! <user@example.com>'],
        'name with unicode characters' => ['José García', 'José García <user@example.com>'],
        'name with emoji' => ['John 😀 Doe', 'John 😀 Doe <user@example.com>'],
        'very long name' => [
            str_repeat('A', 100),
            str_repeat('A', 100) . ' <user@example.com>',
        ],
        'name with angle brackets' => ['John <Admin> Doe', '"John <Admin> Doe" <user@example.com>'],
        'name with tab characters' => ["John\tDoe", "\"John\tDoe\" <user@example.com>"],
        'empty string name' => ['', '"" <user@example.com>'],
        'whitespace only name' => ['   ', '"   " <user@example.com>'],
    ]);

    it('wraps a display name containing an address list in a single quoted-string', function (): void {
        $address = new Address('real@example.com', 'x <attacker@evil.com>, y');

        expect($address->toString())->toBe('"x <attacker@evil.com>, y" <real@example.com>');
    });

    it('quotes display names containing RFC 5322 specials', function (string $name, string $expected): void {
        expect(new Address('user@example.com', $name)->formatDisplayName())->toBe($expected);
    })->with([
        'comma' => ['Doe, John', '"Doe, John"'],
        'semicolon' => ['a;b', '"a;b"'],
        'at sign' => ['team@work', '"team@work"'],
        'period' => ['John Q. Public', '"John Q. Public"'],
        'colon' => ['Re: x', '"Re: x"'],
        'parentheses' => ['John (Admin)', '"John (Admin)"'],
        'square brackets' => ['[bot]', '"[bot]"'],
        'backslash and quote are escaped' => ['a\\b"c', '"a\\\\b\\"c"'],
    ]);

    it('leaves a plain atext display name unquoted', function (string $name): void {
        expect(new Address('user@example.com', $name)->formatDisplayName())->toBe($name);
    })->with([
        'simple' => ['John Doe'],
        'apostrophe' => ["O'Brien"],
        'atext symbols' => ['John & Jane Doe!'],
        'unicode' => ['José García'],
    ]);
});
