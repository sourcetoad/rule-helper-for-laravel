<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\RequiredIf;
use Sourcetoad\RuleHelper\Rule;
use Sourcetoad\RuleHelper\Support\Facades\RuleSet;
use Sourcetoad\RuleHelper\Tests\TestCase;

/**
 * @covers \Sourcetoad\RuleHelper\Rule
 * @covers \Sourcetoad\RuleHelper\RuleSet
 * @covers \Sourcetoad\RuleHelper\ServiceProvider
 * @covers \Sourcetoad\RuleHelper\Support\Facades\RuleSet
 */
class RuleTest extends TestCase
{
    /**
     * @dataProvider ruleDataProvider
     */
    public function testRuleIntegration($data, callable $rules, bool $fails, ?array $errors = null): void
    {
        // Arrange
        if (!is_array($data)) {
            $data = ['field' => $data];
        }

        $rules = $rules();
        if (!is_array($rules)) {
            $rules = ['field' => $rules];
        }

        $validator = Validator::make($data, $rules);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals($fails, $validatorFailed, 'Failed asserting that validator failed.');

        if ($errors) {
            $this->assertEquals($errors, $validator->errors()->toArray());
        }
    }

    /**
     * @dataProvider requireIfProvider
     */
    public function testRequiredIfExtensions(string $data, RequiredIf $rule, bool $fails): void
    {
        // Arrange
        $validator = Validator::make([
            'field' => $data,
        ], [
            'field' => $rule,
        ]);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals($fails, $validatorFailed, 'Failed asserting that validator failed.');
    }

    /**
     * @dataProvider dateProvider
     */
    public function testDateRules(string $data, string $rule, bool $fails): void
    {
        // Arrange
        $validator = Validator::make([
            'field' => $data,
        ], [
            'field' => $rule,
        ]);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals($fails, $validatorFailed, 'Failed asserting that validator failed.');
    }

    public function ruleDataProvider(): array
    {
        return [
            'accepted valid' => [
                'data' => '1',
                'rules' => fn() => RuleSet::create()->accepted(),
                'fails' => false,
            ],
            'accepted invalid' => [
                'data' => '',
                'rules' => fn() => RuleSet::create()->accepted(),
                'fails' => true,
            ],
            'activeUrl valid' => [
                'data' => 'https://www.example.com/',
                'rules' => fn() => RuleSet::create()->activeUrl(),
                'fails' => false,
            ],
            'activeUrl invalid' => [
                'data' => 'https://'.str_repeat((string)Str::uuid(), 3).'/',
                'rules' => fn() => RuleSet::create()->activeUrl(),
                'fails' => true,
            ],
            'after valid' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->after('2021-01-01'),
                'fails' => false,
            ],
            'after invalid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->after('2021-01-01'),
                'fails' => true,
            ],
            'after valid with DateTime' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->after(new DateTime('2021-01-01')),
                'fails' => false,
            ],
            'after invalid with DateTime' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->after(new DateTime('2021-01-01')),
                'fails' => true,
            ],
            'after valid with Carbon' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->after(CarbonImmutable::parse('2021-01-01')),
                'fails' => false,
            ],
            'after invalid with Carbon' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->after(CarbonImmutable::parse('2021-01-01')),
                'fails' => true,
            ],
            'afterOrEqual valid' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->afterOrEqual('2021-01-02'),
                'fails' => false,
            ],
            'afterOrEqual invalid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->afterOrEqual('2021-01-02'),
                'fails' => true,
            ],
            'afterOrEqual valid with DateTime' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->afterOrEqual(new DateTime('2021-01-02')),
                'fails' => false,
            ],
            'afterOrEqual invalid with DateTime' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->afterOrEqual(new DateTime('2021-01-02')),
                'fails' => true,
            ],
            'alpha valid' => [
                'data' => 'alpha',
                'rules' => fn() => RuleSet::create()->alpha(),
                'fails' => false,
            ],
            'alpha invalid' => [
                'data' => 'not-alpha',
                'rules' => fn() => RuleSet::create()->alpha(),
                'fails' => true,
            ],
            'alphaDash valid' => [
                'data' => 'still-alpha',
                'rules' => fn() => RuleSet::create()->alphaDash(),
                'fails' => false,
            ],
            'alphaDash invalid' => [
                'data' => 'not/alpha',
                'rules' => fn() => RuleSet::create()->alphaDash(),
                'fails' => true,
            ],
            'alphaNum valid' => [
                'data' => 'alpha1',
                'rules' => fn() => RuleSet::create()->alphaNum(),
                'fails' => false,
            ],
            'alphaNum invalid' => [
                'data' => 'not-alpha1',
                'rules' => fn() => RuleSet::create()->alphaNum(),
                'fails' => true,
            ],
            'array valid' => [
                'data' => [
                    'field' => ['value'],
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->array(),
                ],
                'fails' => false,
            ],
            'array invalid' => [
                'data' => [
                    'field' => 'value',
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->array(),
                ],
                'fails' => true,
            ],
            'array with keys valid' => [
                'data' => [
                    'field' => ['key1' => 'value1', 'key2' => 'value2'],
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->array('key1', 'key2'),
                ],
                'fails' => false,
            ],
            'array with keys invalid' => [
                'data' => [
                    'field' => ['key1' => 'value1', 'key3' => 'value3'],
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->array('key1', 'key2'),
                ],
                'fails' => true,
            ],
            'bail not set' => [
                'data' => 11,
                'rules' => fn() => RuleSet::create()->max(1)->string(),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field must not be greater than 1 characters.',
                        'The field must be a string.',
                    ],
                ],
            ],
            'bail set' => [
                'data' => 11,
                'rules' => fn() => RuleSet::create()->bail()->max(1)->string(),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field must not be greater than 1 characters.',
                    ],
                ],
            ],
        ];
    }

    public function requireIfProvider(): array
    {
        return [
            'requiredIfAny required with no data' => [
                'data' => '',
                'rule' => Rule::requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => true,
            ],
            'requiredIfAny required with data (callback)' => [
                'data' => 'not empty',
                'rule' => Rule::requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAny required with data (bool)' => [
                'data' => 'not empty',
                'rule' => Rule::requiredIfAny(
                    Rule::requiredIf(false),
                    Rule::requiredIf(true),
                ),
                'fails' => false,
            ],
            'requiredIfAny not required' => [
                'data' => '',
                'rule' => Rule::requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => false),
                ),
                'fails' => false,
            ],
            'requiredIfAll required with no data' => [
                'data' => '',
                'rule' => Rule::requiredIfAll(
                    Rule::requiredIf(fn() => true),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => true,
            ],
            'requiredIfAll required with data (callback)' => [
                'data' => 'not empty',
                'rule' => Rule::requiredIfAll(
                    Rule::requiredIf(fn() => true),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAll required with data (bool)' => [
                'data' => 'not empty',
                'rule' => Rule::requiredIfAll(
                    Rule::requiredIf(true),
                    Rule::requiredIf(true),
                ),
                'fails' => false,
            ],
            'requiredIfAll not required (mismatch)' => [
                'data' => '',
                'rule' => Rule::requiredIfAll(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAll not required (false)' => [
                'data' => '',
                'rule' => Rule::requiredIfAll(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => false),
                ),
                'fails' => false,
            ],
        ];
    }

    public function dateProvider(): array
    {
        return [
            'before success 01/01 is before 01/02 (string)' => [
                'data' => '2021-01-01',
                'rule' => Rule::before('2021-02-02'),
                'fails' => false,
            ],
            'before failure 01/02 is not before 01/02 (string)' => [
                'data' => '2021-01-02',
                'rule' => Rule::before('2021-01-02'),
                'fails' => true,
            ],
            'before failure 01/03 is not before 01/02 (string)' => [
                'data' => '2021-01-03',
                'rule' => Rule::before('2021-01-02'),
                'fails' => true,
            ],
            'before success 01/01 is before 01/02 (DateTime)' => [
                'data' => '2021-01-01',
                'rule' => Rule::before(new DateTime('2021-02-02')),
                'fails' => false,
            ],
            'before failure 01/02 is not before 01/02 (DateTime)' => [
                'data' => '2021-01-02',
                'rule' => Rule::before(new DateTime('2021-01-02')),
                'fails' => true,
            ],
            'before failure 01/03 is not before 01/02 (DateTime)' => [
                'data' => '2021-01-03',
                'rule' => Rule::before(new DateTime('2021-01-02')),
                'fails' => true,
            ],
            'before success 9:59 UTC is before 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 09:59:59 +00:00',
                'rule' => Rule::before(CarbonImmutable::parse('2021-01-03 05:00:00 -05:00')),
                'fails' => false,
            ],
            'before failure 10:00 UTC is not before 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 10:00:00 +00:00',
                'rule' => Rule::before(CarbonImmutable::parse('2021-01-03 05:00:00 -05:00')),
                'fails' => true,
            ],
            'before failure 10:01 UTC is not before 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 10:00:01 +00:00',
                'rule' => Rule::before(CarbonImmutable::parse('2021-01-03 05:00:00 -05:00')),
                'fails' => true,
            ],
            'beforeOrEqual success 9:59 UTC is before 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 09:59:59 +00:00',
                'rule' => Rule::beforeOrEqual(
                    CarbonImmutable::create(2021, 1, 3, 5, 0, 0, 'America/New_York')
                ),
                'fails' => false,
            ],
            'beforeOrEqual success 10:00 UTC is equal to 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 10:00:00 +00:00',
                'rule' => Rule::beforeOrEqual(
                    CarbonImmutable::create(2021, 1, 3, 5, 0, 0, 'America/New_York')
                ),
                'fails' => false,
            ],
            'beforeOrEqual failure 10:01 UTC is not before 5:00 EST (Carbon)' => [
                'data' => '2021-01-03 10:01:00 +00:00',
                'rule' => Rule::beforeOrEqual(
                    CarbonImmutable::create(2021, 1, 3, 5, 0, 0, 'America/New_York')
                ),
                'fails' => true,
            ],
            'dateEquals success 01/01 is 01/01 9:55 (Carbon)' => [
                'data' => '2021-01-01',
                'rule' => Rule::dateEquals(
                    CarbonImmutable::create(2021, 1, 1, 9, 55, 0, 'America/New_York')
                ),
                'fails' => false,
            ],
            'dateEquals success 01/01 is 01/01 23:55 (Carbon)' => [
                'data' => '2021-01-01',
                'rule' => Rule::dateEquals(
                    CarbonImmutable::create(2021, 1, 1, 23, 55, 0, 'America/New_York')
                ),
                'fails' => false,
            ],
            'dateEquals failure 01/02 is not 01/01 (Carbon)' => [
                'data' => '2021-01-02',
                'rule' => Rule::dateEquals(
                    CarbonImmutable::create(2021, 1, 1, 9, 55, 0, 'America/New_York')
                ),
                'fails' => true,
            ],
        ];
    }
}
