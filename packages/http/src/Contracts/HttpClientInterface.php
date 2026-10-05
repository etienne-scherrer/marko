<?php

declare(strict_types=1);

namespace Marko\Http\Contracts;

use Marko\Http\Exceptions\ConnectionException;
use Marko\Http\Exceptions\HttpException;
use Marko\Http\Exceptions\InvalidRequestOptionException;
use Marko\Http\HttpResponse;
use Marko\Http\RequestOptions;

/**
 * Accepted option keys are defined by RequestOptions. Unknown keys throw
 * InvalidRequestOptionException. A 4xx/5xx response throws HttpException
 * unless the request passes 'http_errors' => false.
 *
 * @see RequestOptions
 */
interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function request(
        string $method,
        string $url,
        array $options = [],
    ): HttpResponse;

    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function get(
        string $url,
        array $options = [],
    ): HttpResponse;

    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function post(
        string $url,
        array $options = [],
    ): HttpResponse;

    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function put(
        string $url,
        array $options = [],
    ): HttpResponse;

    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function patch(
        string $url,
        array $options = [],
    ): HttpResponse;

    /**
     * @param array<string, mixed> $options
     *
     * @throws HttpException|ConnectionException|InvalidRequestOptionException
     */
    public function delete(
        string $url,
        array $options = [],
    ): HttpResponse;
}
