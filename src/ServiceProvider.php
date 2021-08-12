<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Contracts\RuleSet::class, RuleSet::class);
    }
}
