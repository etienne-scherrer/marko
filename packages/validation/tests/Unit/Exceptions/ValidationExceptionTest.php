<?php

declare(strict_types=1);

use Marko\Core\Exceptions\HttpExceptionInterface;
use Marko\Routing\Http\ExceptionRenderer;
use Marko\Routing\Http\Request;
use Marko\Validation\Exceptions\ValidationException;
use Marko\Validation\Validation\ValidationErrors;

it('stores errors correctly', function () {
    $errors = new ValidationErrors();
    $errors->add('email', 'Invalid email');

    $exception = new ValidationException('Validation failed', $errors);

    expect($exception->errors())->toBe($errors);
});

it('stores message correctly', function () {
    $errors = new ValidationErrors();
    $exception = new ValidationException('Custom message', $errors);

    expect($exception->getMessage())->toBe('Custom message');
});

it('stores context correctly', function () {
    $errors = new ValidationErrors();
    $exception = new ValidationException('Message', $errors, 'Test context');

    expect($exception->getContext())->toBe('Test context');
});

it('stores suggestion correctly', function () {
    $errors = new ValidationErrors();
    $exception = new ValidationException('Message', $errors, '', 'Try again');

    expect($exception->getSuggestion())->toBe('Try again');
});

it('has empty context by default', function () {
    $errors = new ValidationErrors();
    $exception = new ValidationException('Message', $errors);

    expect($exception->getContext())->toBe('');
});

it('has empty suggestion by default', function () {
    $errors = new ValidationErrors();
    $exception = new ValidationException('Message', $errors);

    expect($exception->getSuggestion())->toBe('');
});

it('creates exception with errors using factory method', function () {
    $errors = new ValidationErrors();
    $errors->add('name', 'Name is required');

    $exception = ValidationException::withErrors($errors);

    expect($exception->getMessage())->toBe('The given data was invalid.')
        ->and($exception->errors())->toBe($errors)
        ->and($exception->getContext())->not->toBeEmpty()
        ->and($exception->getSuggestion())->not->toBeEmpty();
});

describe('HTTP mapping', function (): void {
    it('implements HttpExceptionInterface with status 422', function (): void {
        $exception = ValidationException::withErrors(new ValidationErrors(['email' => ['Email is required.']]));

        expect($exception)->toBeInstanceOf(HttpExceptionInterface::class)
            ->and($exception->getStatusCode())->toBe(422)
            ->and($exception->getHeaders())->toBeEmpty();
    });

    it('exposes message and errors in response data', function (): void {
        $errors = new ValidationErrors(['email' => ['Email is required.'], 'name' => ['Name is too short.']]);

        expect(ValidationException::withErrors($errors)->getResponseData())->toBe([
            'message' => 'The given data was invalid.',
            'errors' => $errors->all(),
        ]);
    });

    it('renders 422 with an errors object matching ValidationErrors::all', function (): void {
        $errors = new ValidationErrors(['email' => ['Email is required.', 'Email is invalid.']]);

        $response = (new ExceptionRenderer())->render(
            ValidationException::withErrors($errors),
            new Request(server: ['HTTP_ACCEPT' => 'application/json']),
        );
        $body = json_decode($response->body(), true);

        expect($response->statusCode())->toBe(422)
            ->and($body['message'])->toBe('The given data was invalid.')
            ->and($body['errors'])->toBe($errors->all());
    });
});
