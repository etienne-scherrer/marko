<?php

declare(strict_types=1);

use Marko\Mail\Contracts\MailerInterface;
use Marko\Mail\Log\LogMailer;
use Marko\Mail\Log\ProductionGuard;

return [
    'bindings' => [
        MailerInterface::class => LogMailer::class,
    ],
    'boot' => function (ProductionGuard $guard): void {
        $guard->check();
    },
];
