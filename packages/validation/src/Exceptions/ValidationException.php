<?php

declare(strict_types=1);

namespace Marko\Validation\Exceptions;

use Exception;
use Marko\Core\Exceptions\HttpExceptionInterface;
use Marko\Validation\Validation\ValidationErrors;
use Throwable;

/**
 * Rendered by the routing pipeline as 422 Unprocessable Content with the
 * field errors under `errors`.
 */
class ValidationException extends Exception implements HttpExceptionInterface
{
    public function __construct(
        string $message,
        private readonly ValidationErrors $errors,
        private readonly string $context = '',
        private readonly string $suggestion = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function withErrors(
        ValidationErrors $errors,
    ): self {
        return new self(
            'The given data was invalid.',
            $errors,
            'Validation failed for one or more fields.',
            'Check the errors() method for details on which fields failed.',
        );
    }

    public function errors(): ValidationErrors
    {
        return $this->errors;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getSuggestion(): string
    {
        return $this->suggestion;
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @return array{message: string, errors: array<string, array<string>>}
     */
    public function getResponseData(): array
    {
        return [
            'message' => $this->getMessage(),
            'errors' => $this->errors->all(),
        ];
    }
}
