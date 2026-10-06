<?php

declare(strict_types=1);

namespace Marko\View\Latte\Extensions;

use Latte\Extension;
use Marko\Routing\UrlGeneratorInterface;

/**
 * Adds a route() function to templates, backed by UrlGeneratorInterface:
 *
 *     <a href={route('shows.show', [id: $show->id])}>...</a>
 */
class RouteExtension extends Extension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * @return array<string, callable>
     */
    public function getFunctions(): array
    {
        return [
            'route' => $this->urlGenerator->route(...),
        ];
    }
}
