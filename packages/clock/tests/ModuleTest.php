<?php

declare(strict_types=1);

use Marko\Clock\SystemClock;
use Marko\Core\Container\Container;
use Psr\Clock\ClockInterface;

$module = require dirname(__DIR__) . '/module.php';

it('binds ClockInterface to SystemClock', function () use ($module): void {
    expect($module['bindings'][ClockInterface::class])->toBe(SystemClock::class);
});

it('registers ClockInterface as a singleton', function () use ($module): void {
    expect($module['singletons'])->toContain(ClockInterface::class);
});

it('resolves one shared SystemClock through the container', function () use ($module): void {
    $container = new Container();

    foreach ($module['bindings'] as $abstract => $concrete) {
        $container->bind($abstract, $concrete);
    }

    foreach ($module['singletons'] as $abstract) {
        $container->singleton($abstract);
    }

    $clock = $container->get(ClockInterface::class);

    expect($clock)->toBeInstanceOf(SystemClock::class)
        ->and($container->get(ClockInterface::class))->toBe($clock);
});
