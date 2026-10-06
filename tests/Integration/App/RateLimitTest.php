<?php

declare(strict_types=1);

use Marko\Routing\Http\Response;

/*
 * RateLimitMiddleware through the real router, counting in real Redis via
 * marko/cache-redis (#165). Redis is shared and never flushed, so every test
 * uses a client address of its own: a fixed one would start already limited
 * on a rerun inside the decay window.
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

/**
 * Send GET $path from $ip through the fixture app.
 */
function rateLimitedRequest(
    object $test,
    string $path,
    string $ip,
): Response {
    return $test->app->router->handle(integrationRequest('GET', $path, server: [
        'REMOTE_ADDR' => $ip,
        'HTTP_ACCEPT' => 'application/json',
    ]));
}

function randomIntegrationIpv4(): string
{
    return '10.' . random_int(0, 255) . '.' . random_int(0, 255) . '.' . random_int(1, 254);
}

function randomIntegrationIpv6(): string
{
    return '2001:db8:' . implode(':', array_map(
        fn (): string => dechex(random_int(1, 0xffff)),
        range(1, 6),
    ));
}

it('returns 429 rather than 500 once the rate limit is hit on cache-redis', function (): void {
    $ip = randomIntegrationIpv4();

    // /limited allows two requests per minute.
    $allowed = [rateLimitedRequest($this, '/limited', $ip), rateLimitedRequest($this, '/limited', $ip)];
    $denied = rateLimitedRequest($this, '/limited', $ip);

    expect(array_map(fn (Response $response): int => $response->statusCode(), $allowed))->toBe([200, 200])
        ->and($allowed[1]->headers()['X-RateLimit-Remaining'])->toBe('0')
        ->and($denied->statusCode())->toBe(429)
        ->and(json_decode($denied->body(), true))->toBe(['message' => 'Too Many Requests'])
        ->and((int) $denied->headers()['Retry-After'])->toBeGreaterThan(0)
        ->and((int) $denied->headers()['Retry-After'])->toBeLessThanOrEqual(60);
})->issue(165);

it('rate limits a client that connects over IPv6', function (): void {
    $ip = randomIntegrationIpv6();

    $first = rateLimitedRequest($this, '/limited', $ip);
    $second = rateLimitedRequest($this, '/limited', $ip);
    $denied = rateLimitedRequest($this, '/limited', $ip);
    $otherClient = rateLimitedRequest($this, '/limited', randomIntegrationIpv6());

    expect($first->statusCode())->toBe(200)
        ->and($first->headers()['X-RateLimit-Remaining'])->toBe('1')
        ->and($second->statusCode())->toBe(200)
        ->and($denied->statusCode())->toBe(429)
        ->and($otherClient->statusCode())->toBe(200);
})->issue(165);

it('keeps separate rate-limit counters for different routes', function (): void {
    $ip = randomIntegrationIpv4();

    rateLimitedRequest($this, '/limited', $ip);
    rateLimitedRequest($this, '/limited', $ip);
    $exhausted = rateLimitedRequest($this, '/limited', $ip);
    $other = rateLimitedRequest($this, '/limited/other', $ip);

    expect($exhausted->statusCode())->toBe(429)
        ->and($other->statusCode())->toBe(200)
        ->and($other->body())->toBe('other limited ok')
        ->and($other->headers()['X-RateLimit-Limit'])->toBe('3')
        ->and($other->headers()['X-RateLimit-Remaining'])->toBe('2');
})->issue(165);
