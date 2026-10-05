<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Tests\Fixtures\InvalidModule;

use Marko\Broadcasting\Attributes\BroadcastChannel;

/** @noinspection PhpUnused - discovered via #[BroadcastChannel] */
#[BroadcastChannel('orders.{orderId}')]
class NotAnAuthorizer {}
