<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule as LaravelRule;
use Illuminate\Validation\Rules\RequiredIf;

class Rule extends LaravelRule
{
    use BuildsDefaultRules;

    /**
     * The field must be present if any of the criteria are true.
     */
    public static function requiredIfAny(RequiredIf ...$rules): RequiredIf
    {
        return self::requiredIf(function () use ($rules) {
            return self::getRuleResults($rules)->containsStrict(true);
        });
    }

    /**
     * The field must be present if all the criteria are true.
     */
    public static function requiredIfAll(RequiredIf ...$rules): RequiredIf
    {
        return self::requiredIf(function () use ($rules) {
            return !self::getRuleResults($rules)->containsStrict(false);
        });
    }

    private static function getRuleResults(array $rules): Collection
    {
        return collect($rules)
            ->map(
                fn($rule) => is_callable($rule->condition)
                    ? call_user_func($rule->condition)
                    : $rule->condition,
            );
    }
}
