<?php

declare(strict_types=1);

/*
 * Behaviour that is broken or missing on develop, recorded as todos so the
 * gap stays visible. Each one names the ticket that owns it. That ticket
 * turns its todo into a real test here (using setUpIntegrationTest() and the
 * fixture under Fixture/) as part of its own exit criteria. Remove the
 * matching fixture workaround (search Fixture/ for the ticket number) at the
 * same time.
 */

pest()->group('integration-services');

// #159 Database connection and entity hydrator are not shared
it('rolls back writes made through two repositories when a transaction spanning both fails')
    ->todo(
        note: 'Resolve TransactionInterface from the container, save an Author via AuthorRepository and a Book via '
            . 'BookRepository inside $transaction->transaction(), then throw. Both tables must be empty afterwards. '
            . 'Today each repository gets its own connection, so the writes are not covered by one transaction.',
        issue: 159,
    );

it('resolves TransactionInterface to the same connection the repositories use')
    ->todo(
        note: 'TransactionInterface, ConnectionInterface and the connection inside every Repository must be one '
            . 'shared instance per process.',
        issue: 159,
    );

// #160 Config ignores real environment variables
it('honours a config value supplied only as a real environment variable')
    ->todo(
        note: 'Set a variable with putenv() (not $_ENV), boot the fixture, and read it back through '
            . 'ConfigRepositoryInterface from a config file that uses the documented env lookup. Variables from the '
            . 'process environment must win over defaults even when variables_order excludes E.',
        issue: 160,
    );

// #161 Database queue retries forever; no worker binding
it('moves a job that always throws to failed_jobs after max_attempts')
    ->todo(
        note: 'Push AlwaysFailingJob, run queue:work --once max_attempts (3) times, releasing its delay between runs. '
            . 'The jobs table must end empty and failed_jobs must hold one row whose exception mentions "failed on '
            . 'purpose". Today the attempt count is never stored, so the job is retried forever.',
        issue: 161,
    );

it('round-trips a job payload containing NUL bytes on postgres')
    ->todo(
        note: 'Push PrivatePropertyJob (its private property puts NUL bytes in serialize() output) and process it '
            . 'with queue:work --once; the marker file must be written. Today Postgres rejects the payload.',
        issue: 161,
    );

// #162 Queue priority and backoff
it('drains a higher-priority queue before a lower-priority one')
    ->todo(
        note: 'Push one RecordingJob to "low" then one to "high"; queue:work --once --queue=high,low must process '
            . 'the "high" job first.',
        issue: 162,
    );

it('waits the configured backoff before retrying a failed job')
    ->todo(
        note: 'With a configured backoff, a failed AlwaysFailingJob must have available_at pushed out by exactly '
            . 'that backoff rather than the hardcoded 2^attempts * 10 seconds.',
        issue: 162,
    );

// #164 Scheduler finds no tasks; overlap protection
it('runs the task registered in the fixture boot callback with schedule:run')
    ->todo(
        note: 'schedule:run must report "Executed: integration heartbeat" and write storage/scheduled-task.txt. '
            . 'Today the command receives a different Schedule instance and reports that no tasks are due.',
        issue: 164,
    );

it('skips a scheduled task whose previous run is still in progress')
    ->todo(
        note: 'With overlap protection enabled on the task, a second schedule:run while the first holds the lock '
            . 'must skip it and say so.',
        issue: 164,
    );

// #165 Rate limiter: Redis, IPv6, per-route limits
it('returns 429 rather than 500 once the rate limit is hit on cache-redis')
    ->todo(
        note: 'Request GET /limited past its limit; the first denied request must be a 429 JSON response with '
            . 'Retry-After. Today the limiter throws TamperedCacheValueException on Redis and the request fails.',
        issue: 165,
    );

it('rate limits a client that connects over IPv6')
    ->todo(
        note: 'GET /limited with REMOTE_ADDR "2001:db8::1" must be counted and limited like an IPv4 client. Today the '
            . 'IPv6 address is rejected as a cache key.',
        issue: 165,
    );

it('keeps separate rate-limit counters for different routes')
    ->todo(
        note: 'Exhausting the limit on one rate-limited route must not deny requests to another route with its own '
            . 'limit.',
        issue: 165,
    );

// #168 Remember-me and auth events
it('issues a remember-me cookie that re-authenticates a later request')
    ->todo(
        note: 'Logging in with remember enabled must set the remember_session cookie; a request carrying only that '
            . 'cookie (no session) must be authenticated as the same user.',
        issue: 168,
    );

it('dispatches login and logout events')
    ->todo(
        note: 'Logging in and out through the guard must dispatch the documented authentication events, observable '
            . 'by a fixture observer.',
        issue: 168,
    );

// #169 Exception to HTTP mapping
it('answers a validation failure with a 422 JSON response')
    ->todo(note: 'A route that fails validation must return 422 with the errors as JSON, not 500.', issue: 169);

it('answers a missing entity with 404')
    ->todo(note: 'A route calling findOrFail() for a missing Author must return 404, not 500.', issue: 169);

it('answers a CSRF failure with 419')
    ->todo(note: 'A state-changing request with a missing or wrong CSRF token must return 419.', issue: 169);
