<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Auth;

/**
 * Collects the authentication events the fixture observers receive. Bound as
 * a singleton in module.php, so a test reads what every request recorded.
 */
class AuthEventLog
{
    /** @var list<string> */
    public private(set) array $entries = [];

    public function record(
        string $entry,
    ): void {
        $this->entries[] = $entry;
    }
}
