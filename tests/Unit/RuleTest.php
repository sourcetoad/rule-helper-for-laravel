<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\RequiredIf;
use Sourcetoad\RuleHelper\Rule;
use Sourcetoad\RuleHelper\Tests\TestCase;

class RuleTest extends TestCase
{
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
        $this->assertEquals($fails, $validatorFailed);
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
        $this->assertEquals($fails, $validatorFailed);
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
