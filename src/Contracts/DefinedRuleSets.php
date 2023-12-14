<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Contracts;

use Sourcetoad\RuleHelper\RuleSet;

interface DefinedRuleSets
{
    public function define(string $name, RuleSet $ruleSet): void;

    public function useDefined(string $name): RuleSet;
}
