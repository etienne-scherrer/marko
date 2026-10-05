<?php

declare(strict_types=1);

use Marko\Clock\SystemClock;
use Psr\Clock\ClockInterface;

it('implements the PSR-20 clock interface', function (): void {
    expect(new SystemClock())->toBeInstanceOf(ClockInterface::class);
});

it('returns the current time', function (): void {
    $before = new DateTimeImmutable();
    $now = new SystemClock()->now();
    $after = new DateTimeImmutable();

    expect($now >= $before)->toBeTrue()
        ->and($now <= $after)->toBeTrue();
});

it('uses the default timezone when none is given', function (): void {
    $original = date_default_timezone_get();

    try {
        date_default_timezone_set('Asia/Tokyo');

        expect(new SystemClock()->now()->getTimezone()->getName())->toBe('Asia/Tokyo');
    } finally {
        date_default_timezone_set($original);
    }
});

it('returns the time in the given timezone', function (): void {
    $clock = new SystemClock(new DateTimeZone('America/New_York'));

    expect($clock->now()->getTimezone()->getName())->toBe('America/New_York');
});

it('accepts a timezone name', function (): void {
    $clock = new SystemClock('Europe/Paris');

    expect($clock->now()->getTimezone()->getName())->toBe('Europe/Paris');
});

it('throws on an invalid timezone name', function (): void {
    new SystemClock('Not/AZone');
})->throws(DateInvalidTimeZoneException::class);
