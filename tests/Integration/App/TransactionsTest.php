<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\TransactionInterface;
use Marko\Database\Exceptions\LockException;
use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Repository\AuthorRepository;

/*
 * Nested transactions, after-commit callbacks and row locks (#176) through
 * the real module wiring against real Postgres: TransactionInterface, the
 * repositories and their query builders all share one connection.
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

function saveIntegrationAuthor(AuthorRepository $repository, string $name): Author
{
    $author = new Author();
    $author->name = $name;
    $repository->save($author);

    return $author;
}

/**
 * @return list<string>
 */
function integrationAuthorNames(ConnectionInterface $connection): array
{
    return array_column($connection->query('SELECT name FROM authors ORDER BY id'), 'name');
}

it('rolls back a savepoint without rolling back the outer transaction', function (): void {
    $container = $this->app->container;
    $transaction = $container->get(TransactionInterface::class);
    $authors = $container->get(AuthorRepository::class);

    $transaction->transaction(function () use ($transaction, $authors): void {
        saveIntegrationAuthor($authors, 'Outer');

        try {
            $transaction->transaction(function () use ($authors): void {
                saveIntegrationAuthor($authors, 'Inner');

                throw new RuntimeException('Inner failure');
            });
        } catch (RuntimeException) {
            // Only the savepoint rolls back.
        }
    });

    expect(integrationAuthorNames($container->get(ConnectionInterface::class)))->toBe(['Outer'])
        ->and($transaction->transactionLevel())->toBe(0);
})->issue(176);

it('runs an after-commit callback only when the transaction commits', function (): void {
    $container = $this->app->container;
    $transaction = $container->get(TransactionInterface::class);
    $authors = $container->get(AuthorRepository::class);
    $log = new ArrayObject();

    try {
        $transaction->transaction(function () use ($transaction, $authors, $log): void {
            saveIntegrationAuthor($authors, 'Rolled back');
            $transaction->afterCommit(fn () => $log->append('rolled-back transaction'));

            throw new RuntimeException('Rollback');
        });
    } catch (RuntimeException) {
        // Expected.
    }

    $transaction->transaction(function () use ($transaction, $authors, $log): void {
        saveIntegrationAuthor($authors, 'Committed');
        $transaction->transaction(
            fn () => $transaction->afterCommit(
                fn () => $log->append('committed at level ' . $transaction->transactionLevel())
            ),
        );
        $log->append('callback deferred');
    });

    expect($log->getArrayCopy())->toBe(['callback deferred', 'committed at level 0'])
        ->and(integrationAuthorNames($container->get(ConnectionInterface::class)))->toBe(['Committed']);
})->issue(176);

it('locks rows through a repository query inside a container-resolved transaction', function (): void {
    $container = $this->app->container;
    $transaction = $container->get(TransactionInterface::class);
    $authors = $container->get(AuthorRepository::class);
    $author = saveIntegrationAuthor($authors, 'Locked');

    $locked = $transaction->transaction(
        fn () => $authors->query()->where('id', '=', $author->id)->lockForUpdate()->firstEntity(),
    );
    $outside = fn () => $authors->query()->where('id', '=', $author->id)->lockForUpdate()->firstEntity();

    expect($locked)->toBeInstanceOf(Author::class)
        ->and($locked?->name)->toBe('Locked')
        ->and($outside)->toThrow(LockException::class, "Cannot lock rows of 'authors' outside a transaction");
})->issue(176);
