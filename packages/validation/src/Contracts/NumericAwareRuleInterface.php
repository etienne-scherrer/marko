<?php

declare(strict_types=1);

namespace Marko\Validation\Contracts;

/**
 * A size rule (`min`, `max`, `between`) whose meaning for a numeric string depends on the
 * field's other rules.
 *
 * By default a string is measured by its length, so `string|min:8` rejects the password `"9"`.
 * When the field also has a `numeric` or `integer` rule, the parser swaps in the rule returned
 * by asNumeric(), which compares numeric strings by value, so `integer|min:18` accepts `"25"`.
 */
interface NumericAwareRuleInterface extends RuleInterface
{
    /**
     * Return the rule with numeric strings compared by value instead of by length.
     */
    public function asNumeric(): RuleInterface;
}
