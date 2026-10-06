<?php

declare(strict_types=1);

return [
    'marko/broadcasting-mercure' => 'Mercure hub broadcasting driver (recommended; FrankenPHP ships a built-in hub, no extra infrastructure)',
    'marko/broadcasting-pusher' => 'Pusher-protocol broadcasting driver (hosted Pusher, Soketi, or Laravel Reverb)',
    'marko/broadcasting-amphp' => 'Self-hosted async SSE broadcasting server on amphp (PHP only; fans out through marko/pubsub)',
];
