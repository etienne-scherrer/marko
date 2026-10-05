<?php

declare(strict_types=1);

use Marko\Core\Command\Input;
use Marko\Core\Command\Output;
use Marko\Log\Command\ClearCommand;
use Marko\Log\Config\LogConfig;
use Marko\Testing\Fake\FakeConfigRepository;

/**
 * @param list<string> $args
 */
function runLogClearCommand(
    string $logPath,
    array $args,
): string {
    $command = new ClearCommand(new LogConfig(new FakeConfigRepository([
        'log.path' => $logPath,
        'log.max_files' => 7,
    ])));
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
