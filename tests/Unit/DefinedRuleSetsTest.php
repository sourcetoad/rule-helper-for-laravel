<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Sourcetoad\RuleHelper\Contracts\DefinedRuleSets;
use Sourcetoad\RuleHelper\RuleHelperServiceProvider;
use Sourcetoad\RuleHelper\RuleSet;
use Sourcetoad\RuleHelper\Tests\Stubs\ExampleNonBackedEnum;
use Sourcetoad\RuleHelper\Tests\Stubs\ExampleStringDuplicateEnum;
use Sourcetoad\RuleHelper\Tests\Stubs\ExampleStringEnum;
use Sourcetoad\RuleHelper\Tests\TestCase;

class DefinedRuleSetsTest extends TestCase
{
    use WithFaker;

    protected function getPackageProviders($app): array
    {
        return [
            RuleHelperServiceProvider::class,
        ];
    }

    public function testCanUseRulesDefinedOutsideOfCurrentRuleSet(): void
    {
        // Arrange
        resolve(DefinedRuleSets::class)->define('user.email', RuleSet::create()->email());

        $validator = Validator::make([
            'field-a' => $this->faker->name(),
        ], [
            'field-a' => RuleSet::useDefined('user.email'),
        ]);

        // Act
        $fails = $validator->fails();
        $messages = $validator->errors();

        // Assert
        $this->assertTrue($fails, 'Failed asserting that RuleSet used defined rule.');
        $this->assertEquals(['field-a' => ['The field-a field must be a valid email address.']], $messages->toArray());
    }

    public function testModifyingDuringUseDoesNotModifyStoredCopy(): void
    {
        // Arrange
        RuleSet::define('user.email', RuleSet::create()->email());

        $validator = Validator::make([], [
            'field-a' => RuleSet::useDefined('user.email')->required(),
            'field-b' => RuleSet::useDefined('user.email'),
        ]);

        // Act
        $fails = $validator->fails();
        $messages = $validator->errors();

        // Assert
        $this->assertTrue($fails, 'Failed asserting that RuleSet used defined rule.');
        $this->assertEquals(['field-a' => ['The field-a field is required.']], $messages->toArray());
    }

    public function testThrowInvalidArgumentExceptionOnUnknownDefinition(): void
    {
        // Expectations
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No rule defined with name user.email');

        // Arrange
        // Nothing to arrange.

        // Act
        RuleSet::useDefined('user.email');

        // Assert
        // No assertions, only expectations.
    }

    public function testConcatDefinedRuleSet(): void
    {
        // Arrange
        RuleSet::define('user.email', RuleSet::create()->email());

        // Act
        $ruleSet = RuleSet::create()->required()->concatDefined('user.email');

        // Assert
        $this->assertSame(['required', 'email'], $ruleSet->toArray());
    }

    public function testWorksWithNonBackedEnums(): void
    {
        // Arrange
        RuleSet::define(ExampleNonBackedEnum::Value, RuleSet::create()->email());

        // Act
        $ruleSet = RuleSet::useDefined(ExampleNonBackedEnum::Value);

        // Assert
        $this->assertSame(['email'], $ruleSet->toArray());
    }

    public function testDefinedEnumsWithDuplicateValuesAreTreatedAsDifferent(): void
    {
        // Arrange
        RuleSet::define(ExampleStringEnum::Another, RuleSet::create()->email());
        RuleSet::define(ExampleStringDuplicateEnum::Another, RuleSet::create()->required());

        // Act
        $ruleSetOne = RuleSet::useDefined(ExampleStringEnum::Another);
        $ruleSetTwo = RuleSet::useDefined(ExampleStringDuplicateEnum::Another);

        // Assert
        $this->assertSame(['email'], $ruleSetOne->toArray());
        $this->assertSame(['required'], $ruleSetTwo->toArray());
    }
}
