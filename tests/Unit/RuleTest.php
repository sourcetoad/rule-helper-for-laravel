<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\User;
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
    public function testRuleIntegration($data, \Closure $rules, bool $fails, ?array $errors = null): void
    {
        // Arrange
        if (!is_array($data)) {
            $data = ['field' => $data];
        }

        $rules = $rules->call($this);
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
            'before valid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->before('2021-01-02'),
                'fails' => false,
            ],
            'before invalid' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->before('2021-01-02'),
                'fails' => true,
            ],
            'before valid with DateTime' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->before(new DateTime('2021-01-02')),
                'fails' => false,
            ],
            'before invalid with DateTime' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->before(new DateTime('2021-01-02')),
                'fails' => true,
            ],
            'before valid with Carbon' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->before(CarbonImmutable::parse('2021-01-02')),
                'fails' => false,
            ],
            'before invalid with Carbon' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->before(CarbonImmutable::parse('2021-01-02')),
                'fails' => true,
            ],
            'beforeOrEqual valid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->beforeOrEqual('2021-01-01'),
                'fails' => false,
            ],
            'beforeOrEqual invalid' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->beforeOrEqual('2021-01-01'),
                'fails' => true,
            ],
            'beforeOrEqual valid with DateTime' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->beforeOrEqual(new DateTime('2021-01-01')),
                'fails' => false,
            ],
            'beforeOrEqual invalid with DateTime' => [
                'data' => '2021-01-02',
                'rules' => fn() => RuleSet::create()->beforeOrEqual(new DateTime('2021-01-01')),
                'fails' => true,
            ],
            'between valid with string' => [
                'data' => str_repeat('.', 10),
                'rules' => fn() => RuleSet::create()->between(9, 11),
                'fails' => false,
            ],
            'between invalid with string' => [
                'data' => str_repeat('.', 12),
                'rules' => fn() => RuleSet::create()->between(9, 11),
                'fails' => true,
            ],
            'boolean valid' => [
                'data' => '1',
                'rules' => fn() => RuleSet::create()->boolean(),
                'fails' => false,
            ],
            'boolean invalid' => [
                'data' => 'please',
                'rules' => fn() => RuleSet::create()->boolean(),
                'fails' => true,
            ],
            'confirmed valid' => [
                'data' => [
                    'field' => 'value',
                    'field_confirmation' => 'value',
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->confirmed(),
                ],
                'fails' => false,
            ],
            'confirmed invalid' => [
                'data' => [
                    'field' => 'value',
                    'field_confirmation' => 'other-value',
                ],
                'rules' => fn() => [
                    'field' => RuleSet::create()->confirmed(),
                ],
                'fails' => true,
            ],
            'currentPassword valid' => [
                'data' => 'password-one',
                'rules' => function () {
                    $this->mockUserAuth('password-one', null);

                    return RuleSet::create()->currentPassword();
                },
                'fails' => false,
            ],
            'currentPassword invalid' => [
                'data' => 'password-one',
                'rules' => function () {
                    $this->mockUserAuth('password-two', null);

                    return RuleSet::create()->currentPassword();
                },
                'fails' => true,
            ],
            'currentPassword with guard valid' => [
                'data' => 'password-one',
                'rules' => function () {
                    $this->mockUserAuth('password-one', 'guardName');

                    return RuleSet::create()->currentPassword('guardName');
                },
                'fails' => false,
            ],
            'currentPassword with guard invalid' => [
                'data' => 'password-a',
                'rules' => function () {
                    $this->mockUserAuth('password-b', 'guardName');

                    return RuleSet::create()->currentPassword('guardName');
                },
                'fails' => true,
            ],
            'date valid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->date(),
                'fails' => false,
            ],
            'date invalid' => [
                'data' => 'a',
                'rules' => fn() => RuleSet::create()->date(),
                'fails' => true,
            ],
            'dateEquals valid' => [
                'data' => '01-Jan-2021',
                'rules' => fn() => RuleSet::create()->dateEquals('2021-01-01'),
                'fails' => false,
            ],
            'dateEquals invalid' => [
                'data' => '02-Jan-2021',
                'rules' => fn() => RuleSet::create()->dateEquals('2021-01-01'),
                'fails' => true,
            ],
            'dateFormat valid' => [
                'data' => '01-Jan-2021',
                'rules' => fn() => RuleSet::create()->dateFormat('d-M-Y'),
                'fails' => false,
            ],
            'dateFormat invalid' => [
                'data' => '2021-01-01',
                'rules' => fn() => RuleSet::create()->dateFormat('d-M-Y'),
                'fails' => true,
            ],
            'different valid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->different('field-b'),
                ],
                'fails' => false,
            ],
            'different invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->different('field-b'),
                ],
                'fails' => true,
            ],
            'digits valid' => [
                'data' => '02021',
                'rules' => fn() => RuleSet::create()->digits(5),
                'fails' => false,
            ],
            'digits invalid' => [
                'data' => 'a2021',
                'rules' => fn() => RuleSet::create()->digits(5),
                'fails' => true,
            ],
            'digitsBetween valid' => [
                'data' => '2021',
                'rules' => fn() => RuleSet::create()->digitsBetween(2, 5),
                'fails' => false,
            ],
            'digitsBetween invalid' => [
                'data' => '2002021',
                'rules' => fn() => RuleSet::create()->digitsBetween(2, 5),
                'fails' => true,
            ],
            'distinct valid' => [
                'data' => [
                    'field' => ['a', 'b', 'c', 'A'],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(),
                ],
                'fails' => false,
            ],
            'distinct invalid' => [
                'data' => [
                    'field' => ['a', 'b', 'c', '1', 1],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(),
                ],
                'fails' => true,
            ],
            'distinct strict valid' => [
                'data' => [
                    'field' => ['a', '1', 1],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(true),
                ],
                'fails' => false,
            ],
            'distinct strict invalid' => [
                'data' => [
                    'field' => ['a', 'b', 'c', 1, 1],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(true),
                ],
                'fails' => true,
            ],
            'distinct ignoreCase valid' => [
                'data' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(false, true),
                ],
                'fails' => false,
            ],
            'distinct ignoreCase invalid' => [
                'data' => [
                    'field' => ['a', 'b', 'c', 'A'],
                ],
                'rules' => fn() => [
                    'field.*' => RuleSet::create()->distinct(false, true),
                ],
                'fails' => true,
            ],
            'email valid' => [
                'data' => 'someone@example.com',
                'rules' => fn() => RuleSet::create()->email(),
                'fails' => false,
            ],
            'email invalid' => [
                'data' => 'someone',
                'rules' => fn() => RuleSet::create()->email(),
                'fails' => true,
            ],
            'email rfc valid' => [
                'data' => 'someone@'.Str::repeat('example', 100).'.com',
                'rules' => fn() => RuleSet::create()->email('rfc'),
                'fails' => false,
            ],
            'email rfc invalid' => [
                'data' => 'someone',
                'rules' => fn() => RuleSet::create()->email('rfc'),
                'fails' => true,
            ],
            'email strict valid' => [
                'data' => 'someone@example.com',
                'rules' => fn() => RuleSet::create()->email('strict'),
                'fails' => false,
            ],
            'email strict invalid' => [
                'data' => 'someone@'.Str::repeat('example', 100).'.com',
                'rules' => fn() => RuleSet::create()->email('strict'),
                'fails' => true,
            ],
            'email dns valid' => [
                'data' => 'someone@gmail.com',
                'rules' => fn() => RuleSet::create()->email('dns'),
                'fails' => false,
            ],
            'email dns invalid' => [
                'data' => 'someone@'.Str::repeat(Str::uuid()->toString(), 3).'.com',
                'rules' => fn() => RuleSet::create()->email('dns'),
                'fails' => true,
            ],
            'email spoof valid' => [
                'data' => 'someone@gmail.com',
                'rules' => fn() => RuleSet::create()->email('spoof'),
                'fails' => false,
            ],
            'email spoof invalid' => [
                'data' => "someone@\u{0430}pple.com",
                'rules' => fn() => RuleSet::create()->email('spoof'),
                'fails' => true,
            ],
            'email filter valid' => [
                'data' => 'someone@gmail.com',
                'rules' => fn() => RuleSet::create()->email('filter'),
                'fails' => false,
            ],
            'email filter invalid' => [
                'data' => "someone@com",
                'rules' => fn() => RuleSet::create()->email('filter'),
                'fails' => true,
            ],
            'endsWith valid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->endsWith('g'),
                'fails' => false,
            ],
            'endsWith invalid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->endsWith('a'),
                'fails' => true,
            ],
            'endsWith any valid' => [
                'data' => 'string-c',
                'rules' => fn() => RuleSet::create()->endsWith('a', 'b', 'c'),
                'fails' => false,
            ],
            'endsWith any invalid' => [
                'data' => 'string-d',
                'rules' => fn() => RuleSet::create()->endsWith('a', 'b', 'c'),
                'fails' => true,
            ],


            'max valid with string' => [
                'data' => str_repeat('.', 10),
                'rules' => fn() => RuleSet::create()->max(10),
                'fails' => false,
            ],
            'max invalid with string' => [
                'data' => str_repeat('.', 11),
                'rules' => fn() => RuleSet::create()->max(10),
                'fails' => true,
            ],
            'max valid with array' => [
                'data' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => RuleSet::create()->max(3),
                'fails' => false,
            ],
            'max invalid with array' => [
                'data' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => RuleSet::create()->max(2),
                'fails' => true,
            ],
            'string valid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->string(),
                'fails' => false,
            ],
            'string invalid' => [
                'data' => 1,
                'rules' => fn() => RuleSet::create()->string(),
                'fails' => true,
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

    private function mockUserAuth(string $password, ?string $guardName): void
    {
        $user = new User();
        /** @noinspection PhpUndefinedFieldInspection */
        $user->password = $password;

        $hasher = $this->mock(Hasher::class)
            ->expects('check')
            ->once()
            ->andReturnUsing(function ($value, $userPassword) {
                return $value === $userPassword;
            })
            ->getMock();

        $guardMock = $this->mock(Guard::class);

        $guardMock
            ->expects('guest')
            ->once()
            ->andReturn(false);

        $guardMock
            ->expects('user')
            ->once()
            ->andReturn($user);

        $authManager = $this->mock(AuthManager::class)
            ->expects('guard')
            ->once()
            ->with($guardName)
            ->andReturn($guardMock)
            ->getMock();

        $this->app->instance('auth', $authManager);
        $this->app->instance('hash', $hasher);
    }
}
