<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Contracts;

interface RuleSet
{
    public function concat(...$rule): self;
}
