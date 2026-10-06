# marko/clock

PSR-20 system clock for Marko--inject `ClockInterface` instead of calling `time()` so time-dependent code can be tested deterministically.

## Installation

```bash
composer require marko/clock
```

## Quick Example

```php
use Psr\Clock\ClockInterface;

class TokenIssuer
{
    public function __construct(
        private ClockInterface $clock,
    ) {}

    public function expiresAt(): DateTimeImmutable
    {
        return $this->clock->now()->modify('+1 hour');
    }
}
```

## Documentation

Full usage, API reference, and examples: [marko/clock](https://marko.build/docs/packages/clock/)
