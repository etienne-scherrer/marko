<?php

declare(strict_types=1);

use Marko\Broadcasting\Exceptions\BroadcastException;

describe('BroadcastException::rejected', function (): void {
    it('names the driver and channel in the message of a rejected publish', function (): void {
        $exception = BroadcastException::rejected('Pusher', 'shows.42', 413, 'Payload too large');

        expect($exception->getMessage())
            ->toBe("Failed to broadcast to channel 'shows.42' via Pusher.");
    });

    it('reports the status and body of a rejected publish in the context', function (): void {
        $exception = BroadcastException::rejected('Pusher', 'shows.42', 413, 'Payload too large');

        expect($exception->getContext())->toContain('HTTP 413')
            ->and($exception->getContext())->toContain('Payload too large');
    });

    it('notes an empty body in the context of a rejected publish', function (): void {
        $exception = BroadcastException::rejected('Mercure', 'shows.42', 403, '');

        expect($exception->getContext())->toContain('(empty body)');
    });

    it('suggests checking credentials when a publish is rejected with 401 or 403', function (int $status): void {
        $exception = BroadcastException::rejected('Mercure', 'shows.42', $status, 'Unauthorized');

        expect($exception->getSuggestion())->toContain('credentials')
            ->and($exception->getSuggestion())->toContain('config/broadcasting-mercure.php');
    })->with([401, 403]);

    it('suggests shrinking the payload when a publish is rejected with 413', function (): void {
        $exception = BroadcastException::rejected('Pusher', 'shows.42', 413, 'Payload too large');

        expect($exception->getSuggestion())->toContain('smaller payload')
            ->and($exception->getSuggestion())->toContain('10 KB');
    });

    it('suggests checking the event, channel and payload for other 4xx rejections', function (int $status): void {
        $exception = BroadcastException::rejected('Pusher', 'shows.42', $status, 'Invalid channel');

        expect($exception->getSuggestion())->toContain('event name, channel name and payload');
    })->with([400, 404, 422]);

    it('reports a server-side failure for 5xx rejections', function (int $status): void {
        $exception = BroadcastException::rejected('Mercure', 'shows.42', $status, 'Internal Server Error');

        expect($exception->getSuggestion())->toContain('server-side failure');
    })->with([500, 502, 503]);

    it('suggests checking the configured url for a redirect', function (): void {
        $exception = BroadcastException::rejected('Mercure', 'shows.42', 301, '');

        expect($exception->getSuggestion())->toContain('config/broadcasting-mercure.php')
            ->and($exception->getSuggestion())->toContain('URL');
    });
});
