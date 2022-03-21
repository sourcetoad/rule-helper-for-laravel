<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use Illuminate\Support\ServiceProvider;

class RuleHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Contracts\DefinedRuleSets::class, DefinedRuleSets::class);
    }
}
