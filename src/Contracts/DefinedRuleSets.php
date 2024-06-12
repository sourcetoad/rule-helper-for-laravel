<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Contracts;

use BackedEnum;
use Sourcetoad\RuleHelper\RuleSet;
use UnitEnum;

interface DefinedRuleSets
{
    public function define(string|BackedEnum|UnitEnum $name, RuleSet $ruleSet): void;

    public function useDefined(string|BackedEnum|UnitEnum $name): RuleSet;
}
