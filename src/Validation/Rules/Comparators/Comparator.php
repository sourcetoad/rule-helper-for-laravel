<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules\Comparators;

interface Comparator
{
    public function canHandle(array $rules): bool;

    public function compare($valueA, $valueB): int;
}
