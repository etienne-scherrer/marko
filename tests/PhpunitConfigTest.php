<?php

declare(strict_types=1);

$phpunitXml = simplexml_load_file(dirname(__DIR__) . '/phpunit.xml');

it(
    'fails the run on deprecations, notices, risky tests and warnings',
    function (string $attribute) use ($phpunitXml): void {
        expect((string) $phpunitXml[$attribute])->toBe('true', "phpunit.xml must set $attribute=\"true\"");
    },
)->with([
    'failOnDeprecation',
    'failOnNotice',
    'failOnPhpunitDeprecation',
    'failOnPhpunitNotice',
    'failOnRisky',
    'failOnWarning',
]);

it('does not fail the run on skipped tests', function () use ($phpunitXml): void {
    // Integration tests skip when their services are unavailable.
    expect((string) $phpunitXml['failOnSkipped'])->not->toBe('true');
});
