<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests;

use Sourcetoad\RuleHelper\ServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
