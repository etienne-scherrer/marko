<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Exceptions\UniqueConstraintViolationException;
use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Repository\AuthorRepository;

/*
 * Constraint violations from the real pgsql driver, through the real module
 * wiring (#177). The fixture schema has no unique column, so each case adds
 * one to its own fresh database instead of changing the shared migrations.
 */

pest()->group('integration-services');

beforeEach(function (): void {
    setUpIntegrationTest($this);

    $this->app->container->get(ConnectionInterface::class)
        ->execute('CREATE UNIQUE INDEX authors_name_unique ON authors (name)');
});

afterEach(fn () => tearDownIntegrationTest($this));

it('throws a typed exception for a unique constraint violation', function (): void {
    $repository = $this->app->container->get(AuthorRepository::class);
    $first = new Author();
    $first->name = 'Octavia E. Butler';
    $repository->save($first);

    $duplicate = new Author();
    $duplicate->name = 'Octavia E. Butler';

    try {
        $repository->save($duplicate);
        $caught = null;
    } catch (UniqueConstraintViolationException $e) {
        $caught = $e;
    }

    expect($caught)->toBeInstanceOf(UniqueConstraintViolationException::class)
        ->and($caught?->constraintName())->toBe('authors_name_unique')
        ->and($caught?->table())->toBe('authors')
        ->and($caught?->getPrevious())->toBeInstanceOf(PDOException::class)
        ->and($caught?->getMessage())->not->toContain('Octavia');
})->issue(177);

it('answers 409 when a route saves a duplicate unique value', function (): void {
    $request = fn () => integrationRequest(
        'GET',
        '/authors/create/Ursula',
        server: ['HTTP_ACCEPT' => 'application/json'],
    );

    $created = $this->app->router->handle($request());
    $conflict = $this->app->router->handle($request());

    expect($created->statusCode())->toBe(201)
        ->and($conflict->statusCode())->toBe(409)
        ->and(json_decode($conflict->body(), true))->toBe(['message' => 'Conflict.'])
        ->and($conflict->body())->not->toContain('authors_name_unique')
        ->and($conflict->body())->not->toContain('Ursula');
})->issue(177);
