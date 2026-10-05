<?php

declare(strict_types=1);

use Marko\Cache\Contracts\CacheInterface;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Job\RecordingJob;
use Marko\Integration\Fixture\Repository\AuthorRepository;
use Marko\Integration\Fixture\Repository\BookRepository;
use Marko\Queue\QueueInterface;

/*
 * Cross-package behaviour that works on develop today, exercised through
 * the real module wiring against real Postgres and Redis.
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

it('applies the fixture migrations with db:migrate', function (): void {
    // setUpIntegrationTest already migrated once; a fresh database proves the
    // command against an empty schema.
    resetIntegrationDatabase();

    $result = runIntegrationCommand($this->app, 'db:migrate', ['--no-generate']);
    $tables = array_column(
        $this->app->container->get(ConnectionInterface::class)->query(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name",
        ),
        'table_name',
    );

    expect($result['exitCode'])->toBe(0, $result['output'])
        ->and($result['output'])->toContain('Applied 5 schema migration(s).')
        ->and($tables)->toBe(['authors', 'books', 'failed_jobs', 'jobs', 'migrations', 'sessions']);
});

it('round-trips an entity through its repository on postgres', function (): void {
    $repository = $this->app->container->get(AuthorRepository::class);

    $author = new Author();
    $author->name = 'Ursula K. Le Guin';
    $repository->save($author);

    $found = $this->app->container->get(AuthorRepository::class)->find((int) $author->id);

    expect($author->id)->toBeInt()
        ->and($found)->toBeInstanceOf(Author::class)
        ->and($found?->name)->toBe('Ursula K. Le Guin');
});

it('runs the fixture seeder with db:seed', function (): void {
    $result = runIntegrationCommand($this->app, 'db:seed');
    $container = $this->app->container;

    expect($result['exitCode'])->toBe(0, $result['output'])
        ->and($container->get(AuthorRepository::class)->findOneBy(['name' => 'Seeded Author']))->not->toBeNull()
        ->and($container->get(BookRepository::class)->findOneBy(['title' => 'Seeded Book']))->not->toBeNull();
});

it('stores and reads a value through the redis cache driver', function (): void {
    // Unique per test: Redis is shared by every parallel worker and by the
    // developer's own data, so the suite never flushes it.
    $key = 'integration.' . bin2hex(random_bytes(8));
    $cache = $this->app->container->get(CacheInterface::class);

    try {
        $cache->set($key, ['answer' => 42], 60);

        expect($this->app->container->get(CacheInterface::class)->get($key))->toBe(['answer' => 42]);
    } finally {
        $cache->delete($key);
    }
});

it('routes a request through the global session middleware and persists the session row', function (): void {
    $first = $this->app->router->handle(integrationRequest('GET', '/visits'));
    $sessionId = integrationCookieValue($first, 'marko_session');

    $second = $this->app->router->handle(integrationRequest('GET', '/visits', cookies: ['marko_session' => (string) $sessionId]));
    $rows = $this->app->container->get(ConnectionInterface::class)->query(
        'SELECT id FROM sessions WHERE id = ?',
        [(string) $sessionId],
    );

    expect($first->body())->toBe('1')
        ->and($sessionId)->not->toBeNull()
        ->and($second->body())->toBe('2')
        ->and($rows)->toHaveCount(1);
});

it('processes a successful job with queue:work --once', function (): void {
    $marker = $this->project . '/storage/recording-job.txt';
    $queue = $this->app->container->get(QueueInterface::class);
    $queue->push(new RecordingJob($marker, 'processed by the worker'));

    expect(file_exists($marker))->toBeFalse();

    $result = runIntegrationCommand($this->app, 'queue:work', ['--once']);

    expect($result['exitCode'])->toBe(0, $result['output'])
        ->and(file_get_contents($marker))->toBe('processed by the worker')
        ->and($queue->size())->toBe(0);
});
