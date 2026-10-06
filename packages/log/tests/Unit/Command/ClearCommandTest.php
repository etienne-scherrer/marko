<?php

declare(strict_types=1);

use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Log\Command\ClearCommand;
use Marko\Log\Config\LogConfig;
use Marko\Testing\Fake\FakeClock;
use Marko\Testing\Fake\FakeConfigRepository;

/**
 * @param list<string> $args
 */
function runLogClearCommand(
    string $logPath,
    array $args,
    ?FakeClock $clock = null,
): string {
    $command = new ClearCommand(new LogConfig(new FakeConfigRepository([
        'log.path' => $logPath,
        'log.max_files' => 7,
    ])), $clock ?? new FakeClock());
    $stream = fopen('php://memory', 'r+');

    $command->execute(new Input($args), new Output($stream));

    rewind($stream);
    $written = stream_get_contents($stream);
    fclose($stream);

    return $written;
}

beforeEach(function (): void {
    $this->logPath = sys_get_temp_dir() . '/marko_log_clear_' . bin2hex(random_bytes(8));
    mkdir($this->logPath, 0755, true);
});

afterEach(function (): void {
    rmdir($this->logPath);
});

it('defaults the retention days to the max_files config', function (): void {
    $written = runLogClearCommand($this->logPath, ['marko', 'log:clear']);

    expect($written)->toContain('older than 7 days');
});

it('accepts the days option with an equals sign', function (): void {
    $written = runLogClearCommand($this->logPath, ['marko', 'log:clear', '--days=3']);

    expect($written)->toContain('older than 3 days');
});

it('accepts the days option as a separate value', function (): void {
    $written = runLogClearCommand($this->logPath, ['marko', 'log:clear', '--days', '3']);

    expect($written)->toContain('older than 3 days');
});

it('deletes only files older than the cutoff on the injected clock', function (): void {
    $clock = new FakeClock('2026-01-21 12:00:00 UTC');
    $now = $clock->now()->getTimestamp();
    $old = $this->logPath . '/app-old.log';
    $boundary = $this->logPath . '/app-boundary.log';
    $recent = $this->logPath . '/app-recent.log';
    touch($old, $now - 3 * 86400 - 1);
    touch($boundary, $now - 3 * 86400);
    touch($recent, $now - 86400);

    $written = runLogClearCommand($this->logPath, ['marko', 'log:clear', '--days=3'], $clock);

    $remaining = array_map(basename(...), glob($this->logPath . '/*.log'));
    array_map(unlink(...), glob($this->logPath . '/*.log'));

    expect($written)->toContain('Deleted 1 log file(s)')
        ->and($remaining)->toEqualCanonicalizing(['app-boundary.log', 'app-recent.log']);
});
