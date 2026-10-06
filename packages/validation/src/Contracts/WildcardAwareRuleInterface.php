<?php

declare(strict_types=1);

namespace Marko\Validation\Contracts;

use InvalidArgumentException;

/**
 * A rule that refers to another field which may contain `*` (e.g. `same:items.*.sku`).
 *
 * Before validating each concrete field, the validator hands the rule the indexes the
 * rules key's wildcards matched, and uses the rule it returns for that field.
 */
interface WildcardAwareRuleInterface extends RuleInterface
{
    /**
     * Return the rule with each `*` in its referenced field replaced, in order, by $indexes.
     *
     * @param string $key The rules key being validated (e.g. `items.*.sku_check`)
     * @param list<string> $indexes The array keys the key's wildcards matched, in order
     *
     * @throws InvalidArgumentException When the referenced field has more wildcards than $indexes
     */
    public function forWildcardIndexes(
        string $key,
        array $indexes,
    ): RuleInterface;
}
