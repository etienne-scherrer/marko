<?php

declare(strict_types=1);

use Marko\Http\HttpResponse;

describe('HttpResponse', function (): void {
    it('creates HttpResponse with status code body and headers', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: 'Hello World',
            headers: ['Content-Type' => 'text/plain'],
        );

        expect($response->statusCode())->toBe(200)
            ->and($response->body())->toBe('Hello World')
            ->and($response->headers())->toBe(['Content-Type' => 'text/plain']);
    });

    it('returns json decoded body from HttpResponse', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '{"name":"marko","version":1}',
        );

        expect($response->json())->toBe(['name' => 'marko', 'version' => 1]);
    });

    it('reports successful status codes from HttpResponse', function (): void {
        $ok = new HttpResponse(statusCode: 200, body: '');
        $created = new HttpResponse(statusCode: 201, body: '');

        expect($ok->isSuccessful())->toBeTrue()
            ->and($ok->isClientError())->toBeFalse()
            ->and($ok->isServerError())->toBeFalse()
            ->and($ok->isRedirect())->toBeFalse()
            ->and($created->isSuccessful())->toBeTrue();
    });

    it('reports client error status codes', function (): void {
        $badRequest = new HttpResponse(statusCode: 400, body: '');
        $notFound = new HttpResponse(statusCode: 404, body: '');

        expect($badRequest->isClientError())->toBeTrue()
            ->and($badRequest->isSuccessful())->toBeFalse()
            ->and($notFound->isClientError())->toBeTrue();
    });

    it('reports server error status codes', function (): void {
        $serverError = new HttpResponse(statusCode: 500, body: '');

        expect($serverError->isServerError())->toBeTrue()
            ->and($serverError->isSuccessful())->toBeFalse()
            ->and($serverError->isClientError())->toBeFalse();
    });

    it('reports redirect status codes', function (): void {
        $movedPermanently = new HttpResponse(statusCode: 301, body: '');
        $found = new HttpResponse(statusCode: 302, body: '');

        expect($movedPermanently->isRedirect())->toBeTrue()
            ->and($movedPermanently->isSuccessful())->toBeFalse()
            ->and($found->isRedirect())->toBeTrue();
    });
});

describe('HttpResponse header values', function (): void {
    it('returns every Set-Cookie value intact and in order from headerValues', function (): void {
        $cookies = [
            'session=abc; Expires=Wed, 21 Oct 2026 07:28:00 GMT; Path=/',
            'theme=dark; Expires=Thu, 22 Oct 2026 07:28:00 GMT; Path=/',
        ];
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headers: ['Set-Cookie' => implode(', ', $cookies)],
            headerValues: ['Set-Cookie' => $cookies],
        );

        expect($response->headerValues('set-cookie'))->toBe($cookies);
    });

    it('matches header names case-insensitively in headerValues', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headerValues: ['Content-Type' => ['application/json']],
        );

        expect($response->headerValues('content-type'))->toBe(['application/json'])
            ->and($response->headerValues('CONTENT-TYPE'))->toBe(['application/json'])
            ->and($response->headerValues('Content-Type'))->toBe(['application/json']);
    });

    it('merges values whose header names differ only in case, in order', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headerValues: ['Set-Cookie' => ['a=1'], 'set-cookie' => ['b=2']],
        );

        expect($response->headerValues('Set-Cookie'))->toBe(['a=1', 'b=2']);
    });

    it('returns an empty list from headerValues for a missing header', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headers: ['Content-Type' => 'text/plain'],
        );

        expect($response->headerValues('X-Missing'))->toBe([]);
    });

    it('returns the comma-joined value from header matching the name case-insensitively', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headerValues: ['Vary' => ['Accept', 'Accept-Encoding']],
        );

        expect($response->header('vary'))->toBe('Accept, Accept-Encoding')
            ->and($response->header('VARY'))->toBe('Accept, Accept-Encoding');
    });

    it('returns null from header for a missing header', function (): void {
        $response = new HttpResponse(statusCode: 200, body: '');

        expect($response->header('X-Missing'))->toBeNull();
    });

    it('derives header values from headers when headerValues is not given', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headers: ['Content-Type' => 'text/plain', 'Vary' => 'Accept, Accept-Encoding'],
        );

        expect($response->headerValues('content-type'))->toBe(['text/plain'])
            ->and($response->headerValues('vary'))->toBe(['Accept, Accept-Encoding'])
            ->and($response->header('Content-Type'))->toBe('text/plain');
    });

    it('keeps headers unchanged when headerValues is given', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headers: ['Set-Cookie' => 'a=1, b=2'],
            headerValues: ['Set-Cookie' => ['a=1', 'b=2']],
        );

        expect($response->headers())->toBe(['Set-Cookie' => 'a=1, b=2']);
    });

    it('derives comma-joined headers from headerValues when headers is not given', function (): void {
        $response = new HttpResponse(
            statusCode: 200,
            body: '',
            headerValues: ['Set-Cookie' => ['a=1', 'b=2'], 'Content-Type' => ['text/plain']],
        );

        expect($response->headers())->toBe(['Set-Cookie' => 'a=1, b=2', 'Content-Type' => 'text/plain']);
    });

    it('returns the whole body as the excerpt when it fits the limit', function (): void {
        $response = new HttpResponse(statusCode: 413, body: 'Payload too large');

        expect($response->bodyExcerpt())->toBe('Payload too large');
    });

    it('truncates the excerpt to the byte limit and notes how many bytes were cut', function (): void {
        $response = new HttpResponse(statusCode: 500, body: str_repeat('a', 30));

        expect($response->bodyExcerpt(10))->toBe('aaaaaaaaaa... [truncated 20 bytes]');
    });

    it('caps the excerpt at 500 bytes by default', function (): void {
        $response = new HttpResponse(statusCode: 500, body: str_repeat('x', 600));

        expect($response->bodyExcerpt())->toBe(str_repeat('x', 500) . '... [truncated 100 bytes]');
    });

    it('does not split a multibyte character when truncating the excerpt', function (): void {
        $response = new HttpResponse(statusCode: 500, body: 'ab€cd');

        expect($response->bodyExcerpt(4))->toBe('ab... [truncated 5 bytes]');
    });

    it('trims surrounding whitespace from the excerpt', function (): void {
        $response = new HttpResponse(statusCode: 502, body: "\n  <h1>Bad Gateway</h1>\n\n");

        expect($response->bodyExcerpt())->toBe('<h1>Bad Gateway</h1>');
    });

    it('returns an empty excerpt for an empty body', function (): void {
        $response = new HttpResponse(statusCode: 500, body: '');

        expect($response->bodyExcerpt())->toBe('');
    });
});
