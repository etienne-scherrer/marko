<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\TransactionInterface;
use Marko\Integration\DatabaseTesting\CreatedEntityLog;
use Marko\Integration\DatabaseTesting\Entity\Show;
use Marko\Integration\DatabaseTesting\Factory\ShowFactory;
use Marko\Integration\DatabaseTesting\Repository\ShowRepository;
use Marko\Integration\DatabaseTesting\Service\PublishShow;
use Marko\Testing\Database\RefreshDatabase;
use Marko\Testing\Database\TestDatabase;

/*
 * RefreshDatabase and EntityFactory (#181) against real Postgres: the fixture
 * is booted and migrated once per process, and each test runs in a
 * transaction that is rolled back afterwards. The tests in this file run in
 * order, so the second "same unique row" test proves the first was rolled back.
 */

pest()->group('integration-services');

beforeEach(function (): void {
    $reason = integrationServicesSkipReason();

    if ($reason !== null) {
        $this->markTestSkipped($reason);
    }

    $this->previousAppEnv = getenv('APP_ENV');
    putenv('APP_ENV=testing');
    $_ENV['APP_ENV'] = 'testing';

    $this->database = TestDatabase::boot(databaseTestingProject());
    $this->refresh = new RefreshDatabase($this->database);
    $this->refresh->begin();
});

afterEach(function (): void {
    if (isset($this->refresh)) {
        $this->refresh->rollback();
    }

    if (isset($this->previousAppEnv)) {
        if ($this->previousAppEnv === false) {
            putenv('APP_ENV');
            unset($_ENV['APP_ENV']);
        } else {
            putenv('APP_ENV=' . $this->previousAppEnv);
            $_ENV['APP_ENV'] = $this->previousAppEnv;
        }
    }
});

function saveShow(
    ShowRepository $repository,
    string $slug,
): Show {
    $show = new Show();
    $show->slug = $slug;
    $show->title = ucfirst($slug);
    $repository->save($show);

    return $show;
}

function countShows(ConnectionInterface $connection): int
{
    return (int) $connection->query('SELECT COUNT(*) AS count FROM shows')[0]['count'];
}

it('inserts a unique row in the first test', function (): void {
    $repository = $this->database->application()->container->get(ShowRepository::class);

    saveShow($repository, 'unique-slug');

    expect(countShows($this->database->connection()))->toBe(1);
})->issue(181);

it('inserts the same unique row in the second test', function (): void {
    $repository = $this->database->application()->container->get(ShowRepository::class);
    $before = countShows($this->database->connection());

    saveShow($repository, 'unique-slug');

    expect($before)->toBe(0)
        ->and(countShows($this->database->connection()))->toBe(1);
})->issue(181);

it('runs migrations once per process', function (): void {
    $project = databaseTestingProject();
    file_put_contents(
        "$project/database/migrations/2026_01_01_000002_create_late_table.php",
        <<<'PHP'
            <?php

            declare(strict_types=1);

            use Marko\Database\Connection\ConnectionInterface;
            use Marko\Database\Migration\Migration;

            return new class () extends Migration
            {
                public function up(ConnectionInterface $connection): void
                {
                    $this->execute($connection, 'CREATE TABLE late_table (id SERIAL PRIMARY KEY)');
                }

                public function down(ConnectionInterface $connection): void
                {
                    $this->execute($connection, 'DROP TABLE IF EXISTS late_table');
                }
            };
            PHP,
    );

    $again = TestDatabase::boot($project);
    $connection = $again->connection();
    $lateTable = $connection->query("SELECT to_regclass('public.late_table') AS name")[0]['name'];
    $batches = $connection->query('SELECT DISTINCT batch FROM migrations');

    expect($again)->toBe($this->database)
        ->and($lateTable)->toBeNull()
        ->and($batches)->toHaveCount(1);
})->issue(181);

it('lets code under test open its own transaction', function (): void {
    $container = $this->database->application()->container;
    $transaction = $container->get(TransactionInterface::class);
    $repository = $container->get(ShowRepository::class);

    $container->get(PublishShow::class)->publish('outer');

    try {
        $transaction->transaction(function () use ($repository): void {
            saveShow($repository, 'inner');

            throw new RuntimeException('inner failure');
        });
    } catch (RuntimeException) {
        // Only the savepoint rolls back.
    }

    $slugs = array_column($this->database->connection()->query('SELECT slug FROM shows ORDER BY id'), 'slug');

    expect($slugs)->toBe(['outer'])
        ->and($transaction->transactionLevel())->toBe(1);
})->issue(181);

it('keeps the test transaction across TestClient requests', function (): void {
    $repository = $this->database->application()->container->get(ShowRepository::class);
    saveShow($repository, 'seeded');
    $client = $this->database->client();

    $client->get('/shows/seeded')->assertOk()->assertSee('Seeded');
    $client->post('/shows/created')->assertStatus(201);
    $client->get('/shows/created')->assertOk()->assertSee('Created');

    expect(countShows($this->database->connection()))->toBe(2)
        ->and($this->database->transaction()->transactionLevel())->toBe(1);
})->issue(181);

it('runs after-commit callbacks only when asked', function (): void {
    $service = $this->database->application()->container->get(PublishShow::class);

    $service->publish('deferred');
    $beforeRun = $service->notified;
    $this->refresh->runAfterCommitCallbacks();

    expect($beforeRun)->toBeEmpty()
        ->and($service->notified)->toBe(['deferred']);
})->issue(181);

it('creates entities through a factory with repository events', function (): void {
    $container = $this->database->application()->container;
    $log = $container->get(CreatedEntityLog::class);
    $log->created = [];
    $factory = new ShowFactory($container);

    $live = $factory->create(fn (Show $show) => $show->status = 'live');
    $more = $factory->sequence(
        fn (Show $show) => $show->status = 'draft',
        fn (Show $show) => $show->status = 'archived',
    )->createMany(2);

    $rows = $this->database->connection()->query('SELECT slug, status FROM shows ORDER BY id');

    expect($live->id)->toBeInt()
        ->and(array_column($more, 'status'))->toBe(['draft', 'archived'])
        ->and($rows)->toBe([
            ['slug' => 'show-1', 'status' => 'live'],
            ['slug' => 'show-2', 'status' => 'draft'],
            ['slug' => 'show-3', 'status' => 'archived'],
        ])
        ->and($log->created)->toBe([Show::class, Show::class, Show::class]);
})->issue(181);
