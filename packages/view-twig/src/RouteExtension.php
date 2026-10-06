<?php

declare(strict_types=1);

namespace Marko\View\Twig;

use Marko\Routing\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Adds a route() function to templates, backed by UrlGeneratorInterface:
 *
 *     <a href="{{ route('shows.show', {id: show.id}) }}">...</a>
 *
 * The result is not marked safe, so it is escaped like any other output.
 */
class RouteExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('route', $this->urlGenerator->route(...)),
        ];
    }
}
