<?php

declare(strict_types=1);

return [
    // Workaround: the shipped default (`null`, meaning "use the auth default
    // guard") makes AuthorizationConfig::defaultGuard() throw, because
    // getString() rejects null, so GateInterface cannot be resolved at all.
    // Found by this suite (#187); remove this file once the default works.
    'default_guard' => 'session',
];
