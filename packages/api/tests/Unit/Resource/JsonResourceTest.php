<?php

declare(strict_types=1);

use Marko\Api\Resource\JsonResource;
use Marko\Routing\Http\Response;

readonly class TestEntity
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}

class TestSecretJsonResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->when(false, $this->resource->email),
            'internal' => $this->missing(),
        ];
    }
}

class TestJsonResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}

it('wraps an entity and exposes it via the resource property', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');
    $resource = new TestJsonResource($entity);

    expect($resource->resource)->toBe($entity);
});

it('serializes entity fields to array via toArray method', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');
    $resource = new TestJsonResource($entity);

    expect($resource->toArray())->toBe(['id' => 1, 'name' => 'Alice']);
});

it('includes conditional fields when condition is true', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'id' => $this->resource->id,
                'email' => $this->when(true, $this->resource->email),
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"id":1,"email":"alice@example.com"}}');
});

it('excludes conditional fields when condition is false', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'id' => $this->resource->id,
                'email' => $this->when(false, $this->resource->email),
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"id":1}}');
});

it('omits fields with MissingValue from output array', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'id' => $this->resource->id,
                'secret' => $this->missing(),
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"id":1}}');
});

it('returns JSON Response via toResponse with correct content type', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');
    $resource = new TestJsonResource($entity);

    $response = $resource->toResponse();

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->headers())->toBe(['Content-Type' => 'application/json'])
        ->and($response->body())->toBe('{"data":{"id":1,"name":"Alice"}}');
});

it('resolves a resource to its filtered array via resolve', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    expect(new TestSecretJsonResource($entity)->resolve())->toBe(['id' => 1]);
});

it('omits when(false) fields from a nested resource', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'id' => $this->resource->id,
                'owner' => new TestSecretJsonResource($this->resource),
            ];
        }
    };

    $body = $resource->toResponse()->body();

    expect($body)->toBe('{"data":{"id":1,"owner":{"id":1}}}')
        ->and($body)->not->toContain('alice@example.com')
        ->and($body)->not->toContain('condition');
});

it('omits when(false) fields from a nested resource wrapped in when(true)', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'owner' => $this->when(true, new TestSecretJsonResource($this->resource)),
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"owner":{"id":1}}}');
});

it('filters ConditionalValue and MissingValue inside nested plain arrays', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'profile' => [
                    'name' => $this->resource->name,
                    'email' => $this->when(false, $this->resource->email),
                    'contact' => [
                        'phone' => $this->missing(),
                        'city' => 'Paris',
                    ],
                ],
            ];
        }
    };

    expect($resource->resolve())->toBe([
        'profile' => [
            'name' => 'Alice',
            'contact' => ['city' => 'Paris'],
        ],
    ])->and($resource->toResponse()->body())->not->toContain('alice@example.com');
});

it('re-indexes nested lists after removing hidden entries so they stay JSON arrays', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'tags' => [
                    $this->when(false, 'hidden'),
                    'visible',
                    $this->missing(),
                    'also-visible',
                ],
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"tags":["visible","also-visible"]}}');
});

it('filters resources nested inside plain arrays', function (): void {
    $entity = new TestEntity(id: 1, name: 'Alice', email: 'alice@example.com');

    $resource = new class ($entity) extends JsonResource
    {
        public function toArray(): array
        {
            return [
                'members' => [
                    new TestSecretJsonResource($this->resource),
                ],
            ];
        }
    };

    expect($resource->toResponse()->body())->toBe('{"data":{"members":[{"id":1}]}}');
});
