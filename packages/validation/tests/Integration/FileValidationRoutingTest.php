<?php

declare(strict_types=1);

namespace Marko\Validation\Tests\Integration\FileValidationRouting;

use Marko\Core\Container\Container;
use Marko\Core\Container\PreferenceRegistry;
use Marko\Core\Discovery\ClassFileParser;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Routing\Http\UploadedFile;
use Marko\Routing\RouteCollection;
use Marko\Routing\RouteDefinition;
use Marko\Routing\RoutingBootstrapper;
use Marko\Validation\Contracts\ValidatorInterface;
use Marko\Validation\Exceptions\ValidationException;
use Marko\Validation\Tests\Support\TestUploads;
use Marko\Validation\Validation\Validator;

class AvatarController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function upload(
        Request $request,
    ): Response {
        $this->validator->validateOrFail([...$request->post(), ...$request->files()], [
            'name' => 'required|string',
            'avatar' => 'required|file|image|max_size:1',
        ]);

        return new Response('stored ' . $request->file('avatar')?->clientFilename());
    }
}

/**
 * @param array<string, UploadedFile> $files
 */
function handleUpload(
    array $files,
): Response {
    $preferenceRegistry = new PreferenceRegistry();
    $container = new Container($preferenceRegistry);
    $container->bind(ValidatorInterface::class, Validator::class);
    $router = new RoutingBootstrapper(
        modules: [],
        container: $container,
        preferenceRegistry: $preferenceRegistry,
        classFileParser: new ClassFileParser(),
    )->boot([]);

    $container->get(RouteCollection::class)->add(new RouteDefinition(
        method: 'POST',
        path: '/avatar',
        controller: AvatarController::class,
        action: 'upload',
    ));

    return $router->handle(new Request(
        server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/avatar', 'HTTP_ACCEPT' => 'application/json'],
        post: ['name' => 'Ada'],
        files: $files,
    ));
}

it('returns 422 with the file errors keyed by field', function (): void {
    $response = handleUpload([
        'avatar' => TestUploads::text(clientFilename: 'avatar.png', clientMediaType: 'image/png'),
    ]);
    $body = json_decode($response->body(), true);

    expect($response->statusCode())->toBe(422)
        ->and($body['errors'])->toBe([
            'avatar' => ['The avatar field must be an image (JPEG, PNG, GIF or WebP).'],
        ]);
});

it('reports a failed upload under its field', function (): void {
    $response = handleUpload(['avatar' => TestUploads::failed(UPLOAD_ERR_INI_SIZE)]);
    $body = json_decode($response->body(), true);

    expect($response->statusCode())->toBe(422)
        ->and($body['errors']['avatar'])->toContain('The avatar file is larger than the server allows.');
});

it('passes a valid upload through to the controller', function (): void {
    $response = handleUpload(['avatar' => TestUploads::png()]);

    expect($response->statusCode())->toBe(200)
        ->and($response->body())->toBe('stored avatar.png');
});
