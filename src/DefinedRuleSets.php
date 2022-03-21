<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

class DefinedRuleSets implements Contracts\DefinedRuleSets
{
    /** @var array<string, RuleSet> */
    private array $definedRules = [];

    public function define(string $name): RuleSet
    {
        return $this->definedRules[$name] = RuleSet::create();
    }

    public function useDefined(string $name): RuleSet
    {
        if (!array_key_exists($name, $this->definedRules)) {
            throw new \InvalidArgumentException('No rule defined with name '.$name);
        }

        return RuleSet::create($this->definedRules[$name]->toArray());
    }
}
