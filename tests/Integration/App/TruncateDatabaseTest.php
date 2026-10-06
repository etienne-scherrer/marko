<?php

declare(strict_types=1);

use Marko\Integration\DatabaseTesting\Factory\ShowFactory;
use Marko\Testing\Database\TestDatabase;
use Marko\Testing\Database\TruncateDatabase;

/*
 * TruncateDatabase (#181) against real Postgres, for tests whose data must be
 * committed. Shares the once-per-process database-testing fixture with
 * RefreshDatabaseTest and leaves its tables empty.
 */

pest()->group('integration-services');

beforeEach(function (): void {
    $reason = integrationServicesSkipReason();

    if ($reason !== null) {
        $this->markTestSkipped($reason);
    }
});

it('truncates entity tables and keeps migration bookkeeping', function (): void {
    withIntegrationAppEnv('testing', function (): void {
        $database = TestDatabase::boot(databaseTestingProject());
        $connection = $database->connection();
        $truncate = new TruncateDatabase($database);
        $factory = new ShowFactory($database->application()->container);
        $migrationsBefore = $connection->query('SELECT name, batch FROM migrations ORDER BY name');

        // No transaction is open, so these rows are committed.
        $factory->createMany(3);
        $truncate->truncate();
        $countAfterTruncate = (int) $connection->query('SELECT COUNT(*) AS count FROM shows')[0]['count'];
        $next = $factory->create();
        $truncate->truncate();

        expect($truncate->tables())->toBe(['shows'])
            ->and($countAfterTruncate)->toBe(0)
            ->and($next->id)->toBe(1)
            ->and($migrationsBefore)->not->toBeEmpty()
            ->and($connection->query('SELECT name, batch FROM migrations ORDER BY name'))->toBe($migrationsBefore);
    });
})->issue(181);
