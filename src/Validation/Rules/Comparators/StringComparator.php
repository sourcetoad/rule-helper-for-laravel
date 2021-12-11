<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules\Comparators;

class StringComparator implements Comparator
{
    public function canHandle(array $rules): bool
    {
        return true;
    }

    public function compare($valueA, $valueB): int
    {
        return strnatcasecmp($valueA, $valueB);
    }
}
