<?php

declare(strict_types=1);

use Marko\Core\Container\ContainerInterface;
use Marko\Log\Config\LogConfig;
use Marko\Log\Contracts\LogFormatterInterface;
use Marko\Log\Formatter\LineFormatter;
use Marko\Log\LogLevel;
use Marko\Log\LogRecord;

it('wires escape_newlines and redact_keys from LogConfig into the LineFormatter binding', function (): void {
    $modulePath = dirname(__DIR__, 2) . '/module.php';
    $module = require $modulePath;
    $binding = $module['bindings'][LogFormatterInterface::class];

    $logConfig = $this->createMock(LogConfig::class);
    $logConfig->expects($this->once())
        ->method('format')
        ->willReturn('[{datetime}] {channel}.{level}: {message} {context}');
    $logConfig->expects($this->once())
        ->method('dateFormat')
        ->willReturn('Y-m-d H:i:s');
    $logConfig->expects($this->once())
        ->method('escapeNewlines')
        ->willReturn(false);
    $logConfig->expects($this->once())
        ->method('redactKeys')
        ->willReturn(['ssn']);

    $container = $this->createMock(ContainerInterface::class);
    $container->expects($this->once())
        ->method('get')
        ->with(LogConfig::class)
        ->willReturn($logConfig);

    $result = $binding($container);
    $output = $result->format(new LogRecord(
        level: LogLevel::Info,
        message: 'm',
        context: ['ssn' => '123-45-6789', 'password' => 'p'],
        datetime: new DateTimeImmutable('2026-01-21 10:30:45'),
        channel: 'app',
    ));

    expect($result)->toBeInstanceOf(LineFormatter::class)
        ->and($result)->toBeInstanceOf(LogFormatterInterface::class)
        ->and($output)->toContain('{"ssn":"[redacted]","password":"p"}');
});
