<?php

declare(strict_types=1);

use Marko\Routing\Exceptions\UrlGenerationException;
use Marko\Routing\RouteCollection;
use Marko\Routing\RouteDefinition;
use Marko\Routing\RoutingConfig;
use Marko\Routing\UrlGenerator;
use Marko\Routing\UrlGeneratorInterface;
use Marko\Testing\Fake\FakeConfigRepository;
use Marko\View\Twig\RouteExtension;
use Marko\View\Twig\TwigEngineFactory;
use Marko\View\Twig\TwigViewConfig;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;

function twigRouteGenerator(): UrlGenerator
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

function twigRouteEnvironment(
    UrlGeneratorInterface $urlGenerator,
    string $template,
): Environment {
    $environment = new Environment(new ArrayLoader(['main' => $template]));
    $environment->addExtension(new RouteExtension($urlGenerator));

    return $environment;
}

describe('RouteExtension', function (): void {
    it('renders a route URL with the route function', function (): void {
        $html = twigRouteEnvironment(twigRouteGenerator(), "{{ route('shows.show', {id: 5}) }}")->render('main');

        expect($html)->toBe('/shows/5');
    });

    it('passes parameters and the absolute flag to the URL generator', function (): void {
        $html = twigRouteEnvironment(twigRouteGenerator(), "{{ route('shows.show', {id: 5, tab: 'cast'}, true) }}")
            ->render('main');

        expect($html)->toBe('https://example.com/shows/5?tab=cast');
    });

    it('escapes the generated URL', function (): void {
        $html = twigRouteEnvironment(
            twigRouteGenerator(),
            "<a href=\"{{ route('shows.show', {id: 5, a: 1, b: 2}) }}\">x</a>",
        )
            ->render('main');

        expect($html)->toBe('<a href="/shows/5?a=1&amp;b=2">x</a>');
    });

    it('lets a URL generation exception fail the render', function (): void {
        try {
            twigRouteEnvironment(twigRouteGenerator(), "{{ route('shows.missing') }}")->render('main');
            $this->fail('Expected the render to fail');
        } catch (RuntimeError $error) {
            expect($error->getPrevious())->toBeInstanceOf(UrlGenerationException::class)
                ->and($error->getPrevious()->getMessage())->toContain("No route named 'shows.missing'");
        }
    });

    it('registers the route extension on the environment', function (): void {
        $config = new TwigViewConfig(new FakeConfigRepository([
            'view.cache_directory' => sys_get_temp_dir() . '/twig-route-cache',
            'view.auto_refresh' => true,
            'view.strict_variables' => true,
            'view.autoescape' => 'html',
            'view.debug' => false,
            'view.charset' => 'UTF-8',
        ]));

        $environment = (new TwigEngineFactory($config, twigRouteGenerator()))->create();
        $environment->setLoader(new ArrayLoader(['main' => "{{ route('shows.show', {id: 9}) }}"]));

        expect($environment->hasExtension(RouteExtension::class))->toBeTrue()
            ->and($environment->render('main'))->toBe('/shows/9');
    });
});
