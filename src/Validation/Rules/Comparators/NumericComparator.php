<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules\Comparators;

use Illuminate\Support\Str;

class NumericComparator implements Comparator
{
    public function canHandle(array $rules): bool
    {
        return (bool) collect($rules)->first(function ($rule) {
            if (!is_string($rule)) {
                return false;
            }

            return $rule === 'integer'
                || $rule === 'numeric'
                || Str::startsWith($rule, 'digits:')
                || Str::startsWith($rule, 'digits_between:');
        });
    }

    public function compare($valueA, $valueB): int
    {
        return $valueA <=> $valueB;
    }
}
