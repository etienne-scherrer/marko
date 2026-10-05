<?php

declare(strict_types=1);

use Marko\Broadcasting\ChannelDefinition;
use Marko\Broadcasting\ChannelRegistry;
use Marko\Broadcasting\Discovery\BroadcastChannelDiscovery;
use Marko\Broadcasting\Exceptions\ChannelAuthorizationException;
use Marko\Broadcasting\Tests\Fixtures\ValidModule\AdminChannelAuthorizer;
use Marko\Broadcasting\Tests\Fixtures\ValidModule\ShowChannelAuthorizer;
use Marko\Core\Container\Container;
use Marko\Core\Discovery\ClassFileParser;
use Marko\Core\Module\ModuleManifest;
use Marko\Core\Module\ModuleRepository;
use Marko\Testing\Fake\FakeAuthenticatable;

function broadcastChannelDiscovery(string $fixture): BroadcastChannelDiscovery
{
    return new BroadcastChannelDiscovery(
        moduleRepository: new ModuleRepository([
            new ModuleManifest(
                name: "test/$fixture",
                version: '1.0.0',
                path: dirname(__DIR__) . "/Fixtures/$fixture",
            ),
        ]),
        classFileParser: new ClassFileParser(),
    );
}

function channelRegistry(): ChannelRegistry
{
    return new ChannelRegistry(
        broadcastChannelDiscovery: broadcastChannelDiscovery('ValidModule'),
        container: new Container(),
    );
}

describe('BroadcastChannelDiscovery', function (): void {
    it('discovers BroadcastChannel classes in module src directories', function (): void {
        $definitions = broadcastChannelDiscovery('ValidModule')->discover();

        $byPattern = [];
        foreach ($definitions as $definition) {
            $byPattern[$definition->pattern] = $definition->authorizerClass;
        }
        ksort($byPattern);

        expect($definitions)->each->toBeInstanceOf(ChannelDefinition::class)
            ->and($byPattern)->toBe([
                'admin' => AdminChannelAuthorizer::class,
                'shows.{showId}.seats.{seatId}' => ShowChannelAuthorizer::class,
            ]);
    });

    it('throws when a BroadcastChannel class does not implement ChannelAuthorizerInterface', function (): void {
        expect(fn () => broadcastChannelDiscovery('InvalidModule')->discover())
            ->toThrow(ChannelAuthorizationException::class, 'must implement ChannelAuthorizerInterface');
    });

    it('throws when two authorizers register the same pattern', function (): void {
        expect(fn () => broadcastChannelDiscovery('DuplicateModule')->discover())
            ->toThrow(ChannelAuthorizationException::class, "Channel pattern 'orders.{orderId}' is registered twice");
    });
});

describe('ChannelRegistry', function (): void {
    it('allows a channel when the authorizer returns true', function (): void {
        expect(channelRegistry()->authorize('admin', new FakeAuthenticatable(id: 1)))->toBeTrue();
    });

    it('denies a channel when the authorizer returns false', function (): void {
        $registry = channelRegistry();

        expect($registry->authorize('admin', new FakeAuthenticatable(id: 2)))->toBeFalse()
            ->and($registry->authorize('admin', null))->toBeFalse();
    });

    it('passes named pattern parameters to the authorizer', function (): void {
        $registry = channelRegistry();
        $user = new FakeAuthenticatable();

        expect($registry->authorize('shows.42.seats.A1', $user))->toBeTrue()
            ->and($registry->authorize('shows.43.seats.A1', $user))->toBeFalse();
    });

    it('does not let a parameter span multiple dot-separated segments', function (): void {
        expect(fn () => channelRegistry()->authorize('shows.4.2.seats.A1', new FakeAuthenticatable()))
            ->toThrow(ChannelAuthorizationException::class);
    });

    it('throws ChannelAuthorizationException for an unknown private channel', function (): void {
        try {
            channelRegistry()->authorize('invoices.9', new FakeAuthenticatable());
            $this->fail('Expected ChannelAuthorizationException');
        } catch (ChannelAuthorizationException $exception) {
            expect($exception->getMessage())->toContain("No authorizer is registered for private channel 'invoices.9'")
                ->and($exception->getSuggestion())->toContain('#[BroadcastChannel(');
        }
    });

    it('lists the registered channel patterns', function (): void {
        $patterns = channelRegistry()->patterns();
        sort($patterns);

        expect($patterns)->toBe(['admin', 'shows.{showId}.seats.{seatId}']);
    });
});
