<?php

declare(strict_types=1);

namespace Marko\View\Twig;

use Marko\Routing\UrlGeneratorInterface;
use Marko\View\CacheDirectoryGuard;
use Marko\View\Exceptions\InsecureCacheDirectoryException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

readonly class TwigEngineFactory
{
    public function __construct(
        private TwigViewConfig $config,
        private UrlGeneratorInterface $urlGenerator,
        private CacheDirectoryGuard $cacheDirectoryGuard,
    ) {}

    /**
     * @throws InsecureCacheDirectoryException
     */
    public function create(): Environment
    {
        $loader = new ArrayLoader();

        $environment = new Environment($loader, [
            'cache' => $this->cacheDirectoryGuard->prepare($this->config->cacheDirectory()),
            'auto_reload' => $this->config->autoRefresh(),
            'strict_variables' => $this->config->strictVariables(),
            'autoescape' => $this->config->autoescape(),
            'debug' => $this->config->debug(),
            'charset' => $this->config->charset(),
        ]);
        $environment->addExtension(new RouteExtension($this->urlGenerator));

        return $environment;
    }
}
