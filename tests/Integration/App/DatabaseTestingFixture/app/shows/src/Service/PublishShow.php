<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Service;

use Marko\Database\Connection\TransactionInterface;
use Marko\Integration\DatabaseTesting\Entity\Show;
use Marko\Integration\DatabaseTesting\Repository\ShowRepository;
use Throwable;

/**
 * Code under test that manages its own transaction and defers work until
 * the commit, the way application services do.
 */
class PublishShow
{
    /** @var list<string> */
    public array $notified = [];

    public function __construct(
        private readonly TransactionInterface $transaction,
        private readonly ShowRepository $showRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function publish(
        string $slug,
    ): Show {
        return $this->transaction->transaction(function () use ($slug): Show {
            $show = new Show();
            $show->slug = $slug;
            $show->title = ucfirst($slug);
            $show->status = 'live';
            $this->showRepository->save($show);

            $this->transaction->afterCommit(function () use ($slug): void {
                $this->notified[] = $slug;
            });

            return $show;
        });
    }
}
