<?php

declare(strict_types=1);

use Marko\Database\MySql\Tests\Fixtures\IntegrationDatabase;

require_once __DIR__ . '/QueueSessionTables.php';

/*
 * A fresh `db:migrate` creates the marko/queue-database and marko/session-database tables on MySQL, and the queue
 * worker, session handler and TruncateDatabase work on them (#337). CI also runs this file against MariaDB 11.8 and
 * 10.11. Runs on the database-mysql driver tests' server (MARKO_TEST_MYSQL_*) in a database of its own; without
 * MARKO_TEST_MYSQL_HOST it skips, and with MARKO_INTEGRATION_REQUIRED set it fails instead. See
 * QueueSessionTables.php for the cases.
 */

pest()->group('integration-services');

beforeEach(function (): void {
    $server = IntegrationDatabase::config();

    if ($server === null) {
        $this->markTestSkipped(IntegrationDatabase::SKIP_REASON);
    }

    setUpQueueSessionTest($this, freshQueueSessionMySqlDatabase($server));
});

afterEach(fn () => tearDownIntegrationTest($this));

describe('queue and session tables on MySQL', function (): void {
    queueSessionTableCases();
});
