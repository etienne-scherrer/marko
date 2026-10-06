<?php

declare(strict_types=1);

namespace Marko\Validation\Validation;

/**
 * Reads a value from validation data by dot path (`user.address.city`, `items.0.sku`).
 *
 * Shared by the validator and the cross-field rules so every rule resolves a field the same way.
 */
class DataPath
{
    /**
     * @param array<mixed> $data
     */
    public static function get(
        array $data,
        string $path,
    ): mixed {
        $value = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Replace each `*` segment of $path, in order, with the next of $indexes.
     *
     * Returns null when $path has more wildcards than there are indexes.
     *
     * @param list<string> $indexes
     */
    public static function fillWildcards(
        string $path,
        array $indexes,
    ): ?string {
        $segments = explode('.', $path);

        foreach ($segments as $position => $segment) {
            if ($segment !== '*') {
                continue;
            }

            if ($indexes === []) {
                return null;
            }

            $segments[$position] = array_shift($indexes);
        }

        return implode('.', $segments);
    }
}
