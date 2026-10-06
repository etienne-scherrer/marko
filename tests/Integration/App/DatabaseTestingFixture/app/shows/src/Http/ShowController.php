<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Http;

use Marko\Database\Exceptions\EntityException;
use Marko\Integration\DatabaseTesting\Repository\ShowRepository;
use Marko\Integration\DatabaseTesting\Service\PublishShow;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Response;
use Throwable;

readonly class ShowController
{
    public function __construct(
        private ShowRepository $showRepository,
        private PublishShow $publishShow,
    ) {}

    /**
     * @throws EntityException
     */
    #[Get('/shows/{slug}')]
    public function show(
        string $slug,
    ): Response {
        $show = $this->showRepository->findOneBy(['slug' => $slug]);

        return $show === null
            ? new Response('not found', 404)
            : new Response($show->title);
    }

    /**
     * @throws Throwable
     */
    #[Post('/shows/{slug}')]
    public function publish(
        string $slug,
    ): Response {
        return new Response((string) $this->publishShow->publish($slug)->id, 201);
    }
}
