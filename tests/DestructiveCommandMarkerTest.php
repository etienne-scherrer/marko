<?php

declare(strict_types=1);

use Marko\Core\Attributes\Command;
use Marko\Database\Command\DestructiveCommandGuard;

/**
 * Every shipped command that changes or deletes stored state must say so with #[Command(destructive: true)], so the
 * MCP run_console_command tool (and any other caller that runs commands on someone else's behalf) can refuse it
 * without guessing from option names.
 */
const DESTRUCTIVE_COMMAND_SUFFIXES = [
    ':clear',
    ':reset',
    ':rollback',
    ':rebuild',
    ':fresh',
    ':seed',
    ':truncate',
    ':purge',
    ':clear-tokens',
];

/**
 * Every #[Command] class shipped in packages/*\/src, keyed by command name.
 *
 * @return array<string, array{class: class-string, attribute: Command}>
 */
function shippedCommands(): array
{
    $commands = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__) . '/packages', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        $path = $file->getPathname();

        if (!str_ends_with($path, '.php') || !preg_match('#/packages/[^/]+/src/#', $path)) {
            continue;
        }

        $source = (string) file_get_contents($path);

        if (!str_contains($source, '#[Command(')
            || !preg_match('/^namespace\s+([^;]+);/m', $source, $namespace)
            || !preg_match('/^(?:readonly\s+|abstract\s+)*class\s+(\w+)/m', $source, $class)
        ) {
            continue;
        }

        /** @var class-string $className */
        $className = $namespace[1] . '\\' . $class[1];
        $attributes = (new ReflectionClass($className))->getAttributes(Command::class);

        if ($attributes === []) {
            continue;
        }

        $attribute = $attributes[0]->newInstance();
        $commands[$attribute->name] = ['class' => $className, 'attribute' => $attribute];
    }

    ksort($commands);

    return $commands;
}

it('finds the shipped commands', function (): void {
    expect(shippedCommands())->toHaveKeys(['cache:clear', 'db:reset', 'list', 'queue:clear']);
});

it('marks every command named like a state-wiping command as destructive', function (): void {
    $unmarked = [];

    foreach (shippedCommands() as $name => $command) {
        $wipesState = array_any(
            DESTRUCTIVE_COMMAND_SUFFIXES,
            fn (string $suffix): bool => str_ends_with($name, $suffix),
        );

        if ($wipesState && !$command['attribute']->destructive) {
            $unmarked[] = "$name ({$command['class']})";
        }
    }

    expect($unmarked)->toBe([], 'Add destructive: true to the #[Command] attribute of: ' . implode(', ', $unmarked));
});

it('marks every command guarded by DestructiveCommandGuard as destructive', function (): void {
    $unmarked = [];

    foreach (shippedCommands() as $name => $command) {
        $constructor = new ReflectionClass($command['class'])->getConstructor();
        $guarded = array_any(
            $constructor?->getParameters() ?? [],
            fn (ReflectionParameter $parameter): bool => $parameter->getType() instanceof ReflectionNamedType
                && $parameter->getType()->getName() === DestructiveCommandGuard::class,
        );

        if ($guarded && !$command['attribute']->destructive) {
            $unmarked[] = "$name ({$command['class']})";
        }
    }

    expect($unmarked)->toBe([], 'Add destructive: true to the #[Command] attribute of: ' . implode(', ', $unmarked));
});

it('keeps read-only commands unmarked', function (): void {
    $commands = shippedCommands();

    foreach (['list', 'module:list', 'cache:status', 'db:status', 'queue:status', 'route:list'] as $name) {
        expect($commands[$name]['attribute']->destructive)->toBeFalse();
    }
});
