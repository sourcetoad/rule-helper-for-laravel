<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules\Comparators;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class DateComparator implements Comparator
{
    public function canHandle(array $rules): bool
    {
        return (bool) collect($rules)->first(function ($rule) {
            if (!is_string($rule)) {
                return false;
            }

            return $rule === 'date'
                || Str::startsWith($rule, 'after:')
                || Str::startsWith($rule, 'after_or_equal:')
                || Str::startsWith($rule, 'date_format:')
                || Str::startsWith($rule, 'before:')
                || Str::startsWith($rule, 'before_or_equal:');
        });
    }

    public function compare($valueA, $valueB): int
    {
        return CarbonImmutable::parse($valueA) <=> CarbonImmutable::parse($valueB);
    }
}
