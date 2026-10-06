<?php

declare(strict_types=1);

use Marko\Log\Contracts\LogFormatterInterface;
use Marko\Log\Formatter\LineFormatter;
use Marko\Log\LogLevel;
use Marko\Log\LogRecord;

it('implements LogFormatterInterface', function (): void {
    $formatter = new LineFormatter();

    expect($formatter)->toBeInstanceOf(LogFormatterInterface::class);
});

it('formats record with default format', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: 'Test message',
        context: ['key' => 'value'],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter();
    $output = $formatter->format($record);

    expect($output)->toBe("[2026-01-21 10:30:45] app.INFO: Test message {\"key\":\"value\"}\n");
});

it('formats record with empty context', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Debug,
        message: 'Simple message',
        context: [],
        datetime: $datetime,
        channel: 'test',
    );

    $formatter = new LineFormatter();
    $output = $formatter->format($record);

    expect($output)->toBe("[2026-01-21 10:30:45] test.DEBUG: Simple message\n");
});

it('interpolates placeholders in message', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: 'User {username} logged in',
        context: ['username' => 'john'],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter();
    $output = $formatter->format($record);

    expect($output)->toContain('User john logged in');
});

it('uses custom format string', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Error,
        message: 'Error occurred',
        context: [],
        datetime: $datetime,
        channel: 'api',
    );

    $formatter = new LineFormatter(
        format: '{level} | {message}',
    );
    $output = $formatter->format($record);

    expect($output)->toBe("ERROR | Error occurred\n");
});

it('uses custom date format', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: 'Test',
        context: [],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(
        format: '[{datetime}] {message}',
        dateFormat: 'd/m/Y H:i',
    );
    $output = $formatter->format($record);

    expect($output)->toBe("[21/01/2026 10:30] Test\n");
});

it('formats all log levels correctly', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $formatter = new LineFormatter(format: '{level}');

    $levels = [
        [LogLevel::Emergency, "EMERGENCY\n"],
        [LogLevel::Alert, "ALERT\n"],
        [LogLevel::Critical, "CRITICAL\n"],
        [LogLevel::Error, "ERROR\n"],
        [LogLevel::Warning, "WARNING\n"],
        [LogLevel::Notice, "NOTICE\n"],
        [LogLevel::Info, "INFO\n"],
        [LogLevel::Debug, "DEBUG\n"],
    ];

    foreach ($levels as [$level, $expected]) {
        $record = new LogRecord(
            level: $level,
            message: 'Test',
            context: [],
            datetime: $datetime,
            channel: 'app',
        );

        expect($formatter->format($record))->toBe($expected);
    }
});

it('handles complex context in JSON', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: 'Test',
        context: [
            'user' => ['id' => 1, 'name' => 'John'],
            'tags' => ['tag1', 'tag2'],
        ],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(format: '{context}');
    $output = $formatter->format($record);

    expect($output)->toContain('"user":{"id":1,"name":"John"}')
        ->and($output)->toContain('"tags":["tag1","tag2"]');
});

it(
    'escapes a newline in the message so the output is a single physical line when escaping is enabled',
    function (): void {
        $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
        $record = new LogRecord(
            level: LogLevel::Info,
            message: "Line one\nLine two",
            context: [],
            datetime: $datetime,
            channel: 'app',
        );

        $formatter = new LineFormatter(
            format: '{message}',
            escapeNewlines: true,
        );
        $output = $formatter->format($record);

        expect($output)->toBe("Line one\\nLine two\n")
            ->and(substr_count($output, "\n"))->toBe(1);
    },
);

it('escapes a carriage return in the message when escaping is enabled', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: "Line one\rLine two",
        context: [],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(
        format: '{message}',
        escapeNewlines: true,
    );
    $output = $formatter->format($record);

    expect($output)->toBe("Line one\\rLine two\n");
});

it('preserves the raw newline in the message when escaping is disabled', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: "Line one\nLine two",
        context: [],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(
        format: '{message}',
        escapeNewlines: false,
    );
    $output = $formatter->format($record);

    expect($output)->toContain("Line one\nLine two");
});

it('escapes newlines embedded in context values when escaping is enabled', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: 'Test',
        context: ['note' => "multi\nline"],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(
        format: '{context}',
        escapeNewlines: true,
    );
    $output = $formatter->format($record);

    expect($output)->not->toContain("\n{")
        ->and(substr_count($output, "\n"))->toBe(1);
});

it(
    'produces exactly one trailing newline per formatted record even when the message ends in a newline',
    function (): void {
        $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
        $record = new LogRecord(
            level: LogLevel::Info,
            message: "Trailing newline message\n",
            context: [],
            datetime: $datetime,
            channel: 'app',
        );

        $formatter = new LineFormatter(
            format: '{message}',
            escapeNewlines: true,
        );
        $output = $formatter->format($record);

        expect(substr_count($output, "\n"))->toBe(1)
            ->and($output)->toEndWith("\n");
    },
);

it('defaults escapeNewlines to true when the LineFormatter is constructed without the flag', function (): void {
    $datetime = new DateTimeImmutable('2026-01-21 10:30:45');
    $record = new LogRecord(
        level: LogLevel::Info,
        message: "Line one\nLine two",
        context: [],
        datetime: $datetime,
        channel: 'app',
    );

    $formatter = new LineFormatter(format: '{message}');
    $output = $formatter->format($record);

    expect($output)->toBe("Line one\\nLine two\n");
});

function formatLineRecord(
    string $message,
    array $context = [],
    string $format = '{message}',
    bool $escapeNewlines = true,
    ?array $redactKeys = null,
): string {
    $record = new LogRecord(
        level: LogLevel::Info,
        message: $message,
        context: $context,
        datetime: new DateTimeImmutable('2026-01-21 10:30:45'),
        channel: 'app',
    );

    $formatter = $redactKeys === null
        ? new LineFormatter(format: $format, escapeNewlines: $escapeNewlines)
        : new LineFormatter(format: $format, escapeNewlines: $escapeNewlines, redactKeys: $redactKeys);

    return $formatter->format($record);
}

it('escapes an ANSI escape sequence interpolated into the message so it cannot drive the terminal', function (): void {
    $output = formatLineRecord('Login failed for {username}', ['username' => "\x1b[2J\x1b[1;31mroot"]);

    expect($output)->not->toContain("\x1b")
        ->and($output)->toStartWith('Login failed for \\x1b[2J\\x1b[1;31mroot');
});

it('escapes every C0 control character and DEL in the message', function (): void {
    $message = '';

    for ($byte = 0; $byte <= 0x1F; $byte++) {
        $message .= chr($byte);
    }

    $output = formatLineRecord($message . "\x7f");

    expect(preg_match('/[\x00-\x1f\x7f]/', rtrim($output, "\n")))->toBe(0)
        ->and($output)->toContain('\\x00')
        ->and($output)->toContain('\\x07')
        ->and($output)->toContain('\\t')
        ->and($output)->toContain('\\n')
        ->and($output)->toContain('\\r')
        ->and($output)->toContain('\\x1b')
        ->and($output)->toContain('\\x1f')
        ->and($output)->toContain('\\x7f');
});

it('escapes C1 controls and the U+2028/U+2029 line terminators in the message', function (): void {
    $output = formatLineRecord("a\u{85}b\u{9b}c\u{2028}d\u{2029}e");

    expect($output)->toBe("a\\u0085b\\u009bc\\u2028d\\u2029e\n");
});

it('escapes raw C1 bytes when the message is not valid UTF-8', function (): void {
    $output = formatLineRecord("a\x9b31mb\xe9");

    expect($output)->toBe("a\\x9b31mb\xe9\n");
});

it('escapes backslashes before controls so a literal backslash-n differs from an escaped newline', function (): void {
    $literal = formatLineRecord('a\\nb');
    $newline = formatLineRecord("a\nb");

    expect($literal)->toBe("a\\\\nb\n")
        ->and($newline)->toBe("a\\nb\n")
        ->and($literal)->not->toBe($newline);
});

it('still escapes non-newline controls when newline escaping is disabled', function (): void {
    $output = formatLineRecord("one\ntwo\x1b[0m", escapeNewlines: false);

    expect($output)->toBe("one\ntwo\\x1b[0m\n");
});

it('leaves printable unicode in the message untouched', function (): void {
    expect(formatLineRecord('Café ☕ 日本'))->toBe("Café ☕ 日本\n");
});

it('escapes DEL, C1 controls and line terminators in context JSON while keeping it valid JSON', function (): void {
    $value = "x\x1b\x7f\u{85}\u{2028}\u{2029}y";
    $output = formatLineRecord('m', ['v' => $value], format: '{context}');
    $json = rtrim($output, "\n");

    expect(preg_match('/[\x00-\x1f\x7f\x{80}-\x{9f}\x{2028}\x{2029}]/u', $json))->toBe(0)
        ->and($json)->toContain('\\u001b')
        ->and($json)->toContain('\\u007f')
        ->and($json)->toContain('\\u0085')
        ->and($json)->toContain('\\u2028')
        ->and($json)->toContain('\\u2029')
        ->and(json_decode($json, true, flags: JSON_THROW_ON_ERROR))->toBe(['v' => $value]);
});

it('redacts the default sensitive context keys', function (): void {
    $output = formatLineRecord('m', [
        'password' => 'hunter2',
        'password_confirmation' => 'hunter2',
        'token' => 'tok',
        'secret' => 'sec',
        'api_key' => 'key',
        'authorization' => 'Bearer abc',
        'email' => 'user@example.com',
    ], format: '{context}');

    expect(json_decode(rtrim($output), true))->toBe([
        'password' => '[redacted]',
        'password_confirmation' => '[redacted]',
        'token' => '[redacted]',
        'secret' => '[redacted]',
        'api_key' => '[redacted]',
        'authorization' => '[redacted]',
        'email' => 'user@example.com',
    ]);
});

it('redacts context keys case-insensitively and at any nesting depth', function (): void {
    $output = formatLineRecord('m', [
        'request' => [
            'headers' => ['Authorization' => 'Bearer abc', 'Accept' => 'text/html'],
            'body' => ['user' => ['PASSWORD' => 'hunter2', 'name' => 'john']],
        ],
    ], format: '{context}');

    expect(json_decode(rtrim($output), true))->toBe([
        'request' => [
            'headers' => ['Authorization' => '[redacted]', 'Accept' => 'text/html'],
            'body' => ['user' => ['PASSWORD' => '[redacted]', 'name' => 'john']],
        ],
    ]);
});

it('redacts a sensitive key used as a message placeholder', function (): void {
    $output = formatLineRecord('Reset with {token}', ['token' => 'abc123'], format: '{message} {context}');

    expect($output)->not->toContain('abc123')
        ->and($output)->toBe("Reset with [redacted] {\"token\":\"[redacted]\"}\n");
});

it('uses a custom redact key list and redacts nothing when it is empty', function (): void {
    $custom = formatLineRecord('m', ['ssn' => '123', 'password' => 'p'], format: '{context}', redactKeys: ['SSN']);
    $none = formatLineRecord('m', ['password' => 'p'], format: '{context}', redactKeys: []);

    expect($custom)->toBe("{\"ssn\":\"[redacted]\",\"password\":\"p\"}\n")
        ->and($none)->toBe("{\"password\":\"p\"}\n");
});
