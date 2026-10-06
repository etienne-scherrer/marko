<?php

declare(strict_types=1);

use Marko\Core\Exceptions\MarkoException;
use Marko\Http\Exceptions\InvalidRequestOptionException;
use Marko\Http\RequestOptions;

describe('RequestOptions', function (): void {
    it('exposes every portable option key as a constant', function (): void {
        expect(RequestOptions::SUPPORTED)->toBe([
            RequestOptions::HEADERS,
            RequestOptions::BODY,
            RequestOptions::JSON,
            RequestOptions::FORM_PARAMS,
            RequestOptions::MULTIPART,
            RequestOptions::QUERY,
            RequestOptions::TIMEOUT,
            RequestOptions::CONNECT_TIMEOUT,
            RequestOptions::AUTH,
            RequestOptions::VERIFY,
            RequestOptions::ALLOW_REDIRECTS,
            RequestOptions::PROXY,
            RequestOptions::HTTP_ERRORS,
            RequestOptions::RESOLVE_TO,
        ])->and(RequestOptions::SUPPORTED)->toBe([
            'headers',
            'body',
            'json',
            'form_params',
            'multipart',
            'query',
            'timeout',
            'connect_timeout',
            'auth',
            'verify',
            'allow_redirects',
            'proxy',
            'http_errors',
            'resolve_to',
        ]);
    });

    it('accepts an empty options array', function (): void {
        RequestOptions::validate([]);

        expect(true)->toBeTrue();
    });

    it('accepts every supported option key', function (): void {
        RequestOptions::validate([
            'headers' => ['Accept' => 'application/json'],
            'json' => ['a' => 1],
            'query' => ['page' => 2],
            'timeout' => 5,
            'connect_timeout' => 2,
            'auth' => ['user', 'secret'],
            'verify' => false,
            'allow_redirects' => 3,
            'proxy' => 'http://proxy.local:8080',
            'http_errors' => false,
        ]);

        expect(true)->toBeTrue();
    });

    it('throws InvalidRequestOptionException naming an unknown key and listing the supported keys', function (): void {
        try {
            RequestOptions::validate(['form_param' => ['a' => 1]]);
            test()->fail('Expected InvalidRequestOptionException');
        } catch (InvalidRequestOptionException $e) {
            expect($e)->toBeInstanceOf(MarkoException::class)
                ->and($e->getMessage())->toContain('form_param')
                ->and($e->getContext())->toContain('form_param')
                ->and($e->getSuggestion())->toContain('form_params')
                ->and($e->getSuggestion())->toContain('connect_timeout')
                ->and($e->getSuggestion())->toContain('http_errors');
        }
    });

    it('accepts driver-specific extra keys passed to validate', function (): void {
        RequestOptions::validate(['guzzle' => ['debug' => true]], ['guzzle']);

        expect(fn () => RequestOptions::validate(['guzzle' => []]))
            ->toThrow(InvalidRequestOptionException::class, 'guzzle');
    });

    it('lists driver-specific extra keys among the supported keys in the error', function (): void {
        try {
            RequestOptions::validate(['nope' => true], ['guzzle']);
            test()->fail('Expected InvalidRequestOptionException');
        } catch (InvalidRequestOptionException $e) {
            expect($e->getSuggestion())->toContain('guzzle');
        }
    });

    it('throws when more than one body option is given', function (): void {
        try {
            RequestOptions::validate([
                'json' => ['a' => 1],
                'form_params' => ['b' => 2],
            ]);
            test()->fail('Expected InvalidRequestOptionException');
        } catch (InvalidRequestOptionException $e) {
            expect($e->getMessage())->toContain('json')
                ->and($e->getMessage())->toContain('form_params');
        }
    });

    it('throws when auth is neither a basic pair nor a bearer token', function (mixed $auth): void {
        expect(fn () => RequestOptions::validate(['auth' => $auth]))
            ->toThrow(InvalidRequestOptionException::class, 'auth');
    })->with([
        'string' => ['user:pass'],
        'single item' => [['user']],
        'three items' => [['user', 'pass', 'digest']],
        'non-string pair' => [[1, 2]],
        'empty bearer' => [['bearer' => '']],
        'unknown key' => [['token' => 'abc']],
    ]);

    it('accepts basic and bearer auth', function (): void {
        RequestOptions::validate(['auth' => ['user', 'pass']]);
        RequestOptions::validate(['auth' => ['bearer' => 'token-123']]);

        expect(true)->toBeTrue();
    });

    it('throws when http_errors is not a boolean', function (): void {
        expect(fn () => RequestOptions::validate(['http_errors' => 'no']))
            ->toThrow(InvalidRequestOptionException::class, 'http_errors');
    });

    it('throws when allow_redirects is neither a bool nor a non-negative int', function (mixed $value): void {
        expect(fn () => RequestOptions::validate(['allow_redirects' => $value]))
            ->toThrow(InvalidRequestOptionException::class, 'allow_redirects');
    })->with([
        'string' => ['yes'],
        'negative' => [-1],
    ]);

    it('throws when verify is neither a bool nor a path', function (): void {
        expect(fn () => RequestOptions::validate(['verify' => 1]))
            ->toThrow(InvalidRequestOptionException::class, 'verify');
    });

    it('accepts an IPv4 or IPv6 address for resolve_to', function (string $ip): void {
        RequestOptions::validate(['resolve_to' => $ip, 'allow_redirects' => false]);

        expect(true)->toBeTrue();
    })->with([
        'ipv4' => ['93.184.215.14'],
        'ipv6' => ['2606:2800:21f:cb07:6820:80da:af6b:8b2c'],
    ]);

    it('throws when resolve_to is not an IP address', function (mixed $value): void {
        expect(fn () => RequestOptions::validate(['resolve_to' => $value, 'allow_redirects' => false]))
            ->toThrow(InvalidRequestOptionException::class, "Invalid value for HTTP request option 'resolve_to'");
    })->with([
        'hostname' => ['example.com'],
        'bracketed ipv6' => ['[::1]'],
        'empty' => [''],
        'int' => [2130706433],
        'array' => [['1.2.3.4']],
    ]);

    it('throws when resolve_to is combined with a proxy', function (): void {
        expect(fn () => RequestOptions::validate([
            'resolve_to' => '93.184.215.14',
            'proxy' => 'http://proxy.local:8080',
            'allow_redirects' => false,
        ]))->toThrow(InvalidRequestOptionException::class, "'resolve_to' and 'proxy' cannot be used together");
    });

    it('throws when resolve_to is used without disabling redirects', function (array $options): void {
        expect(fn () => RequestOptions::validate(['resolve_to' => '93.184.215.14', ...$options]))
            ->toThrow(InvalidRequestOptionException::class, "requires 'allow_redirects' => false");
    })->with([
        'redirects left at the default' => [[]],
        'redirects enabled' => [['allow_redirects' => true]],
        'a redirect limit' => [['allow_redirects' => 2]],
    ]);

    it('returns the bearer token from the auth option', function (): void {
        expect(RequestOptions::bearerToken(['auth' => ['bearer' => 'abc']]))->toBe('abc')
            ->and(RequestOptions::bearerToken(['auth' => ['user', 'pass']]))->toBeNull()
            ->and(RequestOptions::bearerToken([]))->toBeNull();
    });

    it('reports whether a request should throw on http errors, defaulting to true', function (): void {
        expect(RequestOptions::throwsOnHttpError([]))->toBeTrue()
            ->and(RequestOptions::throwsOnHttpError(['http_errors' => true]))->toBeTrue()
            ->and(RequestOptions::throwsOnHttpError(['http_errors' => false]))->toBeFalse();
    });
});
