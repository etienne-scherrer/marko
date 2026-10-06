<?php

declare(strict_types=1);

use Marko\Broadcasting\BroadcastableInterface;
use Marko\Broadcasting\BroadcasterInterface;
use Marko\Broadcasting\Channel;
use Marko\Broadcasting\Exceptions\BroadcastException;
use Marko\Broadcasting\PresenceChannel;
use Marko\Broadcasting\PresenceMember;
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

    it('rejects channel names containing URI-template, wildcard or list characters', function (string $name): void {
        expect(fn () => new Channel($name))
            ->toThrow(BroadcastException::class, 'contains a character that is not allowed');
    })->with([
        'opening brace' => ['customers.1{x}'],
        'closing brace' => ['customers.1}'],
        'asterisk' => ['customers.*'],
        'comma' => ['customers.1,foo'],
    ]);

    it('rejects channel names containing whitespace or control characters', function (string $name): void {
        expect(fn () => new PrivateChannel($name))
            ->toThrow(BroadcastException::class, 'contains a character that is not allowed');
    })->with([
        'trailing newline' => ["customers.1\n"],
        'carriage return' => ["customers.1\r"],
        'space' => ['customers 1'],
        'tab' => ["customers.\t1"],
        'null byte' => ["customers.1\0"],
        'delete' => ["customers.1\x7F"],
    ]);

    it('escapes control characters in the invalid channel name message', function (): void {
        expect(fn () => new Channel("customers.1\n"))
            ->toThrow(BroadcastException::class, "Channel name 'customers.1\\n'");
    });

    it('accepts channel names made of letters, digits and common separators', function (string $name): void {
        expect((new Channel($name))->name)->toBe($name);
    })->with([
        ['orders.42'],
        ['private-orders.7'],
        ['user_1.feed-2'],
        ['tenant=acme@eu.orders;v2:live'],
        ['https://example.com/orders/7'],
    ]);

    it('normalizes a string or channel into a channel', function (): void {
        $private = new PrivateChannel('orders.7');

        expect(Channel::from('shows.42'))->toEqual(new Channel('shows.42'))
            ->and(Channel::from($private))->toBe($private);
    });
});

describe('presence', function (): void {
    it('treats a presence channel as an authorized channel subclass', function (): void {
        $channel = new PresenceChannel('room.1');

        expect($channel)->toBeInstanceOf(Channel::class)
            ->and($channel)->not->toBeInstanceOf(PrivateChannel::class)
            ->and($channel->isPrivate())->toBeTrue();
    });

    it('reports only presence channels as presence channels', function (): void {
        expect((new Channel('a'))->isPresence())->toBeFalse()
            ->and((new PrivateChannel('a'))->isPresence())->toBeFalse()
            ->and((new PresenceChannel('a'))->isPresence())->toBeTrue();
    });

    it('normalizes a presence channel without changing its type', function (): void {
        $channel = new PresenceChannel('room.1');

        expect(Channel::from($channel))->toBe($channel);
    });

    it('rejects an empty presence channel name', function (): void {
        expect(fn () => new PresenceChannel(''))->toThrow(BroadcastException::class, 'Channel name must not be empty');
    });

    it('creates a presence member with an id and public info', function (): void {
        $member = new PresenceMember(10, ['name' => 'Mr. Channels']);

        expect($member->id)->toBe(10)
            ->and($member->info)->toBe(['name' => 'Mr. Channels']);
    });

    it('defaults presence member info to an empty array', function (): void {
        expect((new PresenceMember('u1'))->info)->toBeEmpty();
    });

    it('rejects an empty presence member id', function (): void {
        expect(fn () => new PresenceMember(''))->toThrow(
            BroadcastException::class,
            'Presence member id must not be empty',
        );
    });

    it('builds a presenceChannelsUnsupported exception naming the driver and channel', function (): void {
        $exception = BroadcastException::presenceChannelsUnsupported('Mercure', 'room.1');

        expect($exception->getMessage())->toContain('Mercure')->toContain('room.1')
            ->and($exception->getSuggestion())->toContain('PrivateChannel')->toContain('Pusher');
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
