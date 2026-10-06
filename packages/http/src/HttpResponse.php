<?php

declare(strict_types=1);

namespace Marko\Http;

use JsonException;

readonly class HttpResponse
{
    /**
     * @param array<string, string> $headers One string per header name; repeated values joined with ", ".
     *     When empty, headers() joins $headerValues instead.
     * @param array<string, list<string>> $headerValues Every value of each header, in the order received.
     *     When empty, derived from $headers as one-element lists.
     */
    public function __construct(
        private int $statusCode,
        private string $body,
        private array $headers = [],
        private array $headerValues = [],
    ) {}

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * One string per header name, repeated values joined with ", ". When the
     * response was built with only headerValues, they are joined here.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        if ($this->headers === [] && $this->headerValues !== []) {
            return array_map(static fn (array $values): string => implode(', ', $values), $this->headerValues);
        }

        return $this->headers;
    }

    /**
     * Every value of the named header, matched case-insensitively, in the order received.
     * Lossless for repeated headers such as Set-Cookie. Returns [] when the header is absent.
     *
     * @return list<string>
     */
    public function headerValues(
        string $name,
    ): array {
        $all = $this->headerValues !== []
            ? $this->headerValues
            : array_map(static fn (string $value): array => [$value], $this->headers);

        $values = [];

        foreach ($all as $headerName => $headerValues) {
            if (strcasecmp((string) $headerName, $name) === 0) {
                array_push($values, ...$headerValues);
            }
        }

        return $values;
    }

    /**
     * The named header's values joined with ", ", matched case-insensitively.
     * Returns null when the header is absent. Use headerValues() for Set-Cookie.
     */
    public function header(
        string $name,
    ): ?string {
        $values = $this->headerValues($name);

        return $values === [] ? null : implode(', ', $values);
    }

    /**
     * @throws JsonException
     */
    public function json(): mixed
    {
        return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }
}
