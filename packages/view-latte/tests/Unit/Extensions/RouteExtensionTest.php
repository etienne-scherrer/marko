<?php

declare(strict_types=1);

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Marko\Core\Path\ProjectPaths;
use Marko\Routing\Exceptions\UrlGenerationException;
use Marko\Routing\RouteCollection;
use Marko\Routing\RouteDefinition;
use Marko\Routing\RoutingConfig;
use Marko\Routing\UrlGenerator;
use Marko\Routing\UrlGeneratorInterface;
use Marko\Testing\Fake\FakeConfigRepository;
use Marko\View\CacheDirectoryGuard;
use Marko\View\Latte\Extensions\RouteExtension;
use Marko\View\Latte\LatteEngineFactory;
use Marko\View\Latte\LatteViewConfig;
use Marko\View\ViewConfig;

function latteRouteGenerator(): UrlGenerator
{
    $routes = new RouteCollection();
    $routes->add(
        new RouteDefinition(
            method: 'GET',
            path: '/shows/{id:\d+}',
            controller: 'C',
            action: 'show',
            name: 'shows.show',
        ),
    );

    return new UrlGenerator(
        $routes,
        new RoutingConfig(new FakeConfigRepository(['routing.url' => 'https://example.com'])),
    );
}

function latteRouteEngine(
    UrlGeneratorInterface $urlGenerator,
    string $template,
): Engine {
    $engine = new Engine();
    $engine->setLoader(new StringLoader(['main' => $template]));
    $engine->addExtension(new RouteExtension($urlGenerator));

    return $engine;
}

describe('RouteExtension', function (): void {
    test('it renders a route URL with the route function', function (): void {
        $html = latteRouteEngine(latteRouteGenerator(), '{route("shows.show", [id: 5])}')->renderToString('main');

        expect($html)->toBe('/shows/5');
    });

    test('it passes parameters and the absolute flag to the URL generator', function (): void {
        $html = latteRouteEngine(latteRouteGenerator(), '{route("shows.show", [id: 5, tab: "cast"], absolute: true)}')
            ->renderToString('main');

        expect($html)->toBe('https://example.com/shows/5?tab=cast');
    });

    test('it escapes the generated URL in an attribute context', function (): void {
        $html = latteRouteEngine(latteRouteGenerator(), '<a href={route("shows.show", [id: 5, a: 1, b: 2])}>x</a>')
            ->renderToString('main');

        expect($html)->toBe('<a href="/shows/5?a=1&amp;b=2">x</a>');
    });

    test('it lets a URL generation exception fail the render', function (): void {
        latteRouteEngine(latteRouteGenerator(), '{route("shows.missing")}')->renderToString('main');
    })->throws(UrlGenerationException::class, "No route named 'shows.missing'");

    test('it registers the route extension on the engine', function (): void {
        $cacheDir = sys_get_temp_dir() . '/latte-route-' . bin2hex(random_bytes(4));
        mkdir($cacheDir, 0755, true);
        $viewConfig = $this->createStub(ViewConfig::class);
        $viewConfig->method('cacheDirectory')->willReturn($cacheDir);
        $viewConfig->method('autoRefresh')->willReturn(true);
        $latteViewConfig = $this->createStub(LatteViewConfig::class);
        $latteViewConfig->method('strictTypes')->willReturn(true);

        $engine = (new LatteEngineFactory(
            $viewConfig,
            $latteViewConfig,
            latteRouteGenerator(),
            new CacheDirectoryGuard(new ProjectPaths(sys_get_temp_dir())),
        ))->create();
        $engine->setLoader(new StringLoader(['main' => '{route("shows.show", [id: 9])}']));
        $html = $engine->renderToString('main');
        array_map(unlink(...), glob($cacheDir . '/*'));
        rmdir($cacheDir);

        $routeExtensions = array_filter(
            $engine->getExtensions(),
            fn (object $extension): bool => $extension instanceof RouteExtension,
        );

        expect($routeExtensions)->toHaveCount(1)
            ->and($html)->toBe('/shows/9');
    });
});
