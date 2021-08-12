<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Sourcetoad\RuleHelper\RuleSet
 */
class RuleSet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sourcetoad\RuleHelper\Contracts\RuleSet::class;
    }
}
