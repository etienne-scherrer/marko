<?php

declare(strict_types=1);

namespace Marko\Validation\Rules;

use InvalidArgumentException;
use Marko\Validation\Contracts\RuleInterface;
use Marko\Validation\Contracts\WildcardAwareRuleInterface;
use Marko\Validation\Validation\DataPath;

readonly class Same implements WildcardAwareRuleInterface
{
    /**
     * @param string $otherField An absolute dot path; a `*` is replaced by the current row's index
     */
    public function __construct(
        private string $otherField,
    ) {}

    public function passes(
        string $field,
        mixed $value,
        array $data,
    ): bool {
        if ($value === null || $value === '') {
            return true;
        }

        return $value === DataPath::get($data, $this->otherField);
    }

    public function message(
        string $field,
        mixed $value,
    ): string {
        return "The $field field must match the $this->otherField field.";
    }

    /**
     * @throws InvalidArgumentException
     */
    public function forWildcardIndexes(
        string $key,
        array $indexes,
    ): RuleInterface {
        $otherField = DataPath::fillWildcards($this->otherField, $indexes)
            ?? throw new InvalidArgumentException(
                "The same:$this->otherField rule has more wildcards than the $key key it validates",
            );

        return new self($otherField);
    }
}
