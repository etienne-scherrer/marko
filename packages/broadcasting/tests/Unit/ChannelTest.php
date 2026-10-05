<?php

declare(strict_types=1);

use Marko\Broadcasting\BroadcastableInterface;
use Marko\Broadcasting\BroadcasterInterface;
use Marko\Broadcasting\Channel;
use Marko\Broadcasting\Exceptions\BroadcastException;
use Marko\Broadcasting\PrivateChannel;

describe('Channel', function (): void {
    it('creates a public channel with a name', function (): void {
        $channel = new Channel('shows.42');

        expect($channel->name)->toBe('shows.42')
            ->and($channel->isPrivate())->toBeFalse();
    });

    it('treats a private channel as a channel subclass', function (): void {
        $channel = new PrivateChannel('orders.7');

        expect($channel)->toBeInstanceOf(Channel::class)
            ->and($channel->name)->toBe('orders.7')
            ->and($channel->isPrivate())->toBeTrue();
    });

    it('rejects an empty channel name', function (): void {
        expect(fn () => new Channel(''))->toThrow(BroadcastException::class, 'Channel name must not be empty');
    });

    it('normalizes a string or channel into a channel', function (): void {
        $private = new PrivateChannel('orders.7');

        expect(Channel::from('shows.42'))->toEqual(new Channel('shows.42'))
            ->and(Channel::from($private))->toBe($private);
    });
});

describe('contracts', function (): void {
    it('defines BroadcasterInterface with broadcast and dispatch methods', function (): void {
        $reflection = new ReflectionClass(BroadcasterInterface::class);

        expect($reflection->isInterface())->toBeTrue()
            ->and($reflection->hasMethod('broadcast'))->toBeTrue()
            ->and($reflection->hasMethod('dispatch'))->toBeTrue()
            ->and((string) $reflection->getMethod('broadcast')->getParameters()[0]->getType())
            ->toBe('Marko\Broadcasting\Channel|string');
    });

    it('defines BroadcastableInterface with channels, event and payload methods', function (): void {
        $reflection = new ReflectionClass(BroadcastableInterface::class);

        expect($reflection->isInterface())->toBeTrue()
            ->and($reflection->hasMethod('channels'))->toBeTrue()
            ->and($reflection->hasMethod('event'))->toBeTrue()
            ->and($reflection->hasMethod('payload'))->toBeTrue();
    });
});
