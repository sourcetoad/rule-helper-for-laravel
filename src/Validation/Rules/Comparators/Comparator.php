<?php

namespace Sourcetoad\RuleHelper\Validation\Rules\Comparators;

interface Comparator
{
    public function compare($valueA, $valueB): int;

    public function canHandle(string $attribute, array $rules): bool;
}
