<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

class DefinedRuleSets implements Contracts\DefinedRuleSets
{
    /** @var array<string, RuleSet> */
    private array $definedRules = [];

    public function define(string $name, RuleSet $ruleSet): void
    {
        $this->definedRules[$name] = $ruleSet;
    }

    public function useDefined(string $name): RuleSet
    {
        if (!array_key_exists($name, $this->definedRules)) {
            throw new \InvalidArgumentException('No rule defined with name '.$name);
        }

        return $this->definedRules[$name];
    }
}
