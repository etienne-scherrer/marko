<?php

declare(strict_types=1);

namespace Marko\Clock;

use DateInvalidTimeZoneException;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

/**
 * PSR-20 clock that reads the system time.
 *
 * Without a timezone, now() uses the PHP default timezone at the moment it is
 * called, so a later date_default_timezone_set() is respected.
 */
readonly class SystemClock implements ClockInterface
{
    private ?DateTimeZone $timezone;

    /**
     * The union type is deliberate: the container passes the default (null) for a
     * non-class type instead of trying to autowire a DateTimeZone.
     *
     * @throws DateInvalidTimeZoneException When given an unknown timezone name
     */
    public function __construct(
        DateTimeZone|string|null $timezone = null,
    ) {
        $this->timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
