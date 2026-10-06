<?php

declare(strict_types=1);

require_once __DIR__ . '/QueueSessionTables.php';

/*
 * A fresh `db:migrate` creates the marko/queue-database and marko/session-database tables on PostgreSQL, and the
 * queue worker, session handler and TruncateDatabase work on them (#337). Runs on the fixture app's server (DB_*)
 * in a database of its own; see QueueSessionTables.php for the cases.
 */

pest()->group('integration-services');

beforeEach(function (): void {
    $reason = integrationServicesSkipReason();

    if ($reason !== null) {
        $this->markTestSkipped($reason);
    }

    setUpQueueSessionTest($this, freshQueueSessionPgSqlDatabase());
});

afterEach(fn () => tearDownIntegrationTest($this));

describe('queue and session tables on PostgreSQL', function (): void {
    queueSessionTableCases();
});
