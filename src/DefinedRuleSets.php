<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use BackedEnum;
use InvalidArgumentException;
use UnitEnum;

class DefinedRuleSets implements Contracts\DefinedRuleSets
{
    /** @var array<string, RuleSet> */
    private array $definedRules = [];

    public function define(string|BackedEnum|UnitEnum $name, RuleSet $ruleSet): void
    {
        $key = $this->getKey($name);

        $this->definedRules[$key] = $ruleSet;
    }

    public function useDefined(string|BackedEnum|UnitEnum $name): RuleSet
    {
        $key = $this->getKey($name);

        if (!array_key_exists($key, $this->definedRules)) {
            throw new InvalidArgumentException('No rule defined with name '.$key);
        }

        return $this->definedRules[$key];
    }

    private function getKey(string|BackedEnum|UnitEnum $value): string
    {
        if ($value instanceof UnitEnum) {
            return sprintf(
                '%s::%s',
                $value::class,
                $value instanceof BackedEnum ? $value->value : $value->name,
            );
        }

        return $value;
    }
}
