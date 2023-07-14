<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Brick\Math\BigNumber;
use Carbon\CarbonImmutable;
use Closure;
use DateTime;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Password;
use Mockery\MockInterface;
use Sourcetoad\RuleHelper\Rule;
use Sourcetoad\RuleHelper\RuleSet;
use Sourcetoad\RuleHelper\Tests\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypes;

class RuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Password::$defaultCallback = null;
    }

    /**
     * @dataProvider ruleDataProvider
     */
    public function testRuleIntegration($data, Closure $rules, bool $fails, ?array $errors = null): void
    {
        // Arrange
        if ($data instanceof Closure) {
            $data = $data->call($this);
        }
        if (!is_array($data)) {
            $data = ['field' => $data];
        }

        $rules = $rules->call($this);
        if (!is_array($rules)) {
            $rules = ['field' => $rules];
        }

        // TODO Remove this translator override when we're no longer supporting 9.x. We replace the translator rather
        //      than passing in message overrides due to Password pulling its translations from the translator layer
        //      instead of the validator layer.
        /**
         * @var Translator $translator
         */
        $translator = $this->app['translator'];
        /**
         * @var Translator|MockInterface $mockTranslator
         */
        $mockTranslator = $this->mock(Translator::class);
        $mockTranslator
            ->shouldReceive('get')
            ->andReturnUsing(
                fn($key, array $replace = [], $locale = null) => [
                    'validation.max.string' => 'The :attribute field must not be greater than :max characters.',
                    'validation.min.string' => 'The :attribute field must be at least :min characters.',
                    'validation.password.numbers' => 'The :attribute field must contain at least one number.',
                    'validation.password.symbols' => 'The :attribute field must contain at least one symbol.',
                    'validation.string' => 'The :attribute field must be a string.',
                ][$key] ?? $translator->get($key, $replace, $locale),
            );
        $this->app['translator'] = $mockTranslator;

        $validator = Validator::make($data, $rules);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals(
            $fails,
            $validatorFailed,
            'Failed asserting that validator failed.'.PHP_EOL
            .'Validation Errors:'.PHP_EOL
            .$validator->errors()->toJson(JSON_PRETTY_PRINT)
        );

        if ($errors) {
            $this->assertEquals($errors, $validator->errors()->toArray());
        }
    }

    /**
     * @dataProvider excludeProvider
     */
    public function testExcludeRuleIntegration($data, Closure $rules, array $expected): void
    {
        // Arrange
        $rules = $rules->call($this);
        $validator = Validator::make($data, $rules);

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $valid = $validator->validate();

        // Assert
        $this->assertEqualsCanonicalizing($expected, $valid);
    }

    /**
     * @dataProvider requireIfProvider
     */
    public function testRequiredIfExtensions(string $data, Closure $rule, bool $fails): void
    {
        // Arrange
        $validator = Validator::make([
            'field' => $data,
        ], [
            'field' => $rule->call($this),
        ]);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals(
            $fails,
            $validatorFailed,
            'Failed asserting that validator failed.'.PHP_EOL
            .'Validation Errors:'.PHP_EOL
            .$validator->errors()->toJson(JSON_PRETTY_PRINT)
        );
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
        $this->assertEquals(
            $fails,
            $validatorFailed,
            'Failed asserting that validator failed.'.PHP_EOL
            .'Validation Errors:'.PHP_EOL
            .$validator->errors()->toJson(JSON_PRETTY_PRINT)
        );
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
            'acceptedIf accepted' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => 'B',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->acceptedIf('field-b', 'A', 'B', 'C'),
                ],
                'fails' => false,
            ],
            'acceptedIf accept not needed' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'D',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->acceptedIf('field-b', 'A', 'B', 'C'),
                ],
                'fails' => false,
            ],
            'acceptedIf invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'B',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->acceptedIf('field-b', 'A', 'B'),
                ],
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
            'ascii valid' => [
                'data' => 'Ascii',
                'rules' => fn() => RuleSet::create()->ascii(),
                'fails' => false,
            ],
            'ascii invalid non-breaking space' => [
                'data' => 'Ascii with NBSP',
                'rules' => fn() => RuleSet::create()->ascii(),
                'fails' => true,
            ],
            'ascii invalid utf-8' => [
                'data' => 'アスキー',
                'rules' => fn() => RuleSet::create()->ascii(),
                'fails' => true,
            ],
            'bail not set' => [
                'data' => 11,
                'rules' => fn() => RuleSet::create()->max(1)->string(),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must not be greater than 1 characters.',
                        'The field field must be a string.',
                    ],
                ],
            ],
            'bail set' => [
                'data' => 11,
                'rules' => fn() => RuleSet::create()->bail()->max(1)->string(),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must not be greater than 1 characters.',
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
            'between valid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->between(0.1, 0.5),
                'fails' => false,
            ],
            'between invalid with float' => [
                'data' => 0.6,
                'rules' => fn() => RuleSet::create()->numeric()->between(0.1, 0.5),
                'fails' => true,
            ],
            'between valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->between(1, 100),
                'fails' => false,
            ],
            'between invalid with number' => [
                'data' => 101,
                'rules' => fn() => RuleSet::create()->numeric()->between(1, 100),
                'fails' => true,
            ],
            'between valid with string' => [
                'data' => 50,
                'rules' => fn() => RuleSet::create()->numeric()->between('25', '75'),
                'fails' => false,
            ],
            'between invalid with string' => [
                'data' => 76,
                'rules' => fn() => RuleSet::create()->numeric()->between('25', '75'),
                'fails' => true,
            ],
            'between valid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->between(BigNumber::of('9223372036854775808'), BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'between invalid with BigNumber' => [
                'data' => '9223372036854775811',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->between(BigNumber::of('9223372036854775808'), BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'between valid with string length' => [
                'data' => str_repeat('.', 10),
                'rules' => fn() => RuleSet::create()->between(9, 11),
                'fails' => false,
            ],
            'between invalid with string length' => [
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
            'decimal valid' => [
                'data' => '1.1',
                'rules' => fn() => RuleSet::create()->decimal(1),
                'fails' => false,
            ],
            'decimal signed positive' => [
                'data' => '+1.1',
                'rules' => fn() => RuleSet::create()->decimal(1),
                'fails' => false,
            ],
            'decimal signed negative' => [
                'data' => '-1.1',
                'rules' => fn() => RuleSet::create()->decimal(1),
                'fails' => false,
            ],
            'decimal invalid not decimal' => [
                'data' => '1',
                'rules' => fn() => RuleSet::create()->decimal(1),
                'fails' => true,
            ],
            'decimal invalid wrong precision' => [
                'data' => '1.01',
                'rules' => fn() => RuleSet::create()->decimal(1),
                'fails' => true,
            ],
            'decimal invalid with max' => [
                'data' => '1.12345',
                'rules' => fn() => RuleSet::create()->decimal(1, 4),
                'fails' => true,
            ],
            'declined valid' => [
                'data' => '0',
                'rules' => fn() => RuleSet::create()->declined(),
                'fails' => false,
            ],
            'declined invalid' => [
                'data' => '1',
                'rules' => fn() => RuleSet::create()->declined(),
                'fails' => true,
            ],
            'declinedIf declined' => [
                'data' => [
                    'field-a' => '0',
                    'field-b' => 'B',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->declinedIf('field-b', 'A', 'B', 'C'),
                ],
                'fails' => false,
            ],
            'declinedIf decline not needed' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => 'D',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->declinedIf('field-b', 'A', 'B', 'C'),
                ],
                'fails' => false,
            ],
            'declinedIf decline needed' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => 'B',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->declinedIf('field-b', 'A', 'B'),
                ],
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
            'dimensions min_width valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['min_width' => 100]),
                'fails' => false,
            ],
            'dimensions min_width invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['min_width' => 101]),
                'fails' => true,
            ],
            'dimensions min_width via modifier valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions([], fn(Dimensions $rule) => $rule->minWidth(100)),
                'fails' => false,
            ],
            'dimensions min_width via modifier invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions([], fn(Dimensions $rule) => $rule->minWidth(101)),
                'fails' => true,
            ],
            'dimensions max_width valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['max_width' => 100]),
                'fails' => false,
            ],
            'dimensions max_width invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['max_width' => 99]),
                'fails' => true,
            ],
            'dimensions max_height valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['max_height' => 50]),
                'fails' => false,
            ],
            'dimensions max_height invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['max_height' => 49]),
                'fails' => true,
            ],
            'dimensions width valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['width' => 100]),
                'fails' => false,
            ],
            'dimensions width invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['width' => 99]),
                'fails' => true,
            ],
            'dimensions height valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['height' => 50]),
                'fails' => false,
            ],
            'dimensions height invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['height' => 51]),
                'fails' => true,
            ],
            'dimensions ratio fraction valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['ratio' => '2/1']),
                'fails' => false,
            ],
            'dimensions ratio fraction invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['ratio' => '3/1']),
                'fails' => true,
            ],
            'dimensions ratio decimal valid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['ratio' => '2']),
                'fails' => false,
            ],
            'dimensions ratio decimal invalid' => [
                'data' => new File(dirname(__DIR__).'/stubs/100x50.png'),
                'rules' => fn() => RuleSet::create()->dimensions(['ratio' => '1.5']),
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
            'doesntEndWith valid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->doesntEndWith('a'),
                'fails' => false,
            ],
            'doesntEndWith invalid' => [
                'data' => 'string-a',
                'rules' => fn() => RuleSet::create()->doesntEndWith('a'),
                'fails' => true,
            ],
            'doesntEndWith any valid' => [
                'data' => 'string-d',
                'rules' => fn() => RuleSet::create()->doesntEndWith('a', 'b', 'c'),
                'fails' => false,
            ],
            'doesntEndWith any invalid' => [
                'data' => 'string-c',
                'rules' => fn() => RuleSet::create()->doesntEndWith('a', 'b', 'c'),
                'fails' => true,
            ],
            'doesntStartWith valid' => [
                'data' => 'a-string',
                'rules' => fn() => RuleSet::create()->doesntStartWith('s'),
                'fails' => false,
            ],
            'doesntStartWith invalid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->doesntStartWith('s'),
                'fails' => true,
            ],
            'doesntStartWith any valid' => [
                'data' => 'd-string',
                'rules' => fn() => RuleSet::create()->doesntStartWith('a', 'b', 'c'),
                'fails' => false,
            ],
            'doesntStartWith any invalid' => [
                'data' => 'c-string',
                'rules' => fn() => RuleSet::create()->doesntStartWith('a', 'b', 'c'),
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
                'data' => 'someone@example.com',
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
            'file valid' => [
                'data' => new File(__FILE__),
                'rules' => fn() => RuleSet::create()->file(),
                'fails' => false,
            ],
            'file invalid' => [
                'data' => 'not a file',
                'rules' => fn() => RuleSet::create()->file(),
                'fails' => true,
            ],
            'filled valid' => [
                'data' => 'content',
                'rules' => fn() => RuleSet::create()->filled(),
                'fails' => false,
            ],
            'filled invalid' => [
                'data' => '',
                'rules' => fn() => RuleSet::create()->filled(),
                'fails' => true,
            ],
            'gt valid with float' => [
                'data' => 0.6,
                'rules' => fn() => RuleSet::create()->numeric()->gt(0.5),
                'fails' => false,
            ],
            'gt invalid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->gt(0.5),
                'fails' => true,
            ],
            'gt valid with number' => [
                'data' => 101,
                'rules' => fn() => RuleSet::create()->numeric()->gt(100),
                'fails' => false,
            ],
            'gt invalid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->gt(100),
                'fails' => true,
            ],
            'gt valid with BigNumber' => [
                'data' => '9223372036854775811',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->gt(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'gt invalid with BigNumber' => [
                'data' => '9223372036854775810',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->gt(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'gt valid with string' => [
                'data' => [
                    'field-a' => '2',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->gt('field-b'),
                ],
                'fails' => false,
            ],
            'gt invalid with string' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => '2',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->gt('field-b'),
                ],
                'fails' => true,
            ],
            'gte valid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->gte(0.5),
                'fails' => false,
            ],
            'gte invalid with float' => [
                'data' => 0.4,
                'rules' => fn() => RuleSet::create()->numeric()->gte(0.5),
                'fails' => true,
            ],
            'gte valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->gte(100),
                'fails' => false,
            ],
            'gte invalid with number' => [
                'data' => 99,
                'rules' => fn() => RuleSet::create()->numeric()->gte(100),
                'fails' => true,
            ],
            'gte valid with BigNumber' => [
                'data' => '9223372036854775810',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->gte(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'gte invalid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->gte(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'gte valid with string' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->gte('field-b'),
                ],
                'fails' => false,
            ],
            'gte invalid with string' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => '2',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->gte('field-b'),
                ],
                'fails' => true,
            ],
            'image valid' => [
                'data' => fn() => $this->mockFile('/code/image.jpg'),
                'rules' => fn() => RuleSet::create()->image(),
                'fails' => false,
            ],
            'image invalid' => [
                'data' => fn() => $this->mockFile('/code/document.pdf'),
                'rules' => fn() => RuleSet::create()->image(),
                'fails' => true,
            ],
            'in valid' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->in(['a', 'b', 'c']),
                ],
                'fails' => false,
            ],
            'in invalid' => [
                'data' => [
                    'field-a' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->in(['a', 'b', 'c']),
                ],
                'fails' => true,
            ],
            'inArray valid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->inArray('field-b.*'),
                ],
                'fails' => false,
            ],
            'inArray invalid' => [
                'data' => [
                    'field-a' => 'd',
                    'field-b' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->inArray('field-b.*'),
                ],
                'fails' => true,
            ],
            'integer valid' => [
                'data' => '1',
                'rules' => fn() => RuleSet::create()->integer(),
                'fails' => false,
            ],
            'integer invalid' => [
                'data' => 'a',
                'rules' => fn() => RuleSet::create()->integer(),
                'fails' => true,
            ],
            'ip valid' => [
                'data' => [
                    'field-a' => '127.0.0.1',
                    'field-b' => '::1',
                ],
                'rules' => fn() => [
                    'field-a' => fn() => RuleSet::create()->ip(),
                    'field-b' => fn() => RuleSet::create()->ip(),
                ],
                'fails' => false,
            ],
            'ip invalid' => [
                'data' => 'not an ip',
                'rules' => fn() => RuleSet::create()->ip(),
                'fails' => true,
            ],
            'ipv4 valid' => [
                'data' => '127.0.0.1',
                'rules' => fn() => RuleSet::create()->ipv4(),
                'fails' => false,
            ],
            'ipv4 invalid' => [
                'data' => '::1',
                'rules' => fn() => RuleSet::create()->ipv4(),
                'fails' => true,
            ],
            'ipv6 valid' => [
                'data' => '::1',
                'rules' => fn() => RuleSet::create()->ipv6(),
                'fails' => false,
            ],
            'ipv6 invalid' => [
                'data' => '127.0.0.1',
                'rules' => fn() => RuleSet::create()->ipv6(),
                'fails' => true,
            ],
            'json valid' => [
                'data' => '{"a":1}',
                'rules' => fn() => RuleSet::create()->json(),
                'fails' => false,
            ],
            'json invalid' => [
                'data' => '{"a":',
                'rules' => fn() => RuleSet::create()->json(),
                'fails' => true,
            ],
            'lowercase valid' => [
                'data' => 'lowercase',
                'rules' => fn() => RuleSet::create()->lowercase(),
                'fails' => false,
            ],
            'lowercase invalid' => [
                'data' => 'Lowercase',
                'rules' => fn() => RuleSet::create()->lowercase(),
                'fails' => true,
            ],
            'lt valid with float' => [
                'data' => 0.4,
                'rules' => fn() => RuleSet::create()->numeric()->lt(0.5),
                'fails' => false,
            ],
            'lt invalid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->lt(0.5),
                'fails' => true,
            ],
            'lt valid with number' => [
                'data' => 99,
                'rules' => fn() => RuleSet::create()->numeric()->lt(100),
                'fails' => false,
            ],
            'lt invalid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->lt(100),
                'fails' => true,
            ],
            'lt valid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->lt(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'lt invalid with BigNumber' => [
                'data' => '9223372036854775810',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->lt(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'lt valid with string' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => '2',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->lt('field-b'),
                ],
                'fails' => false,
            ],
            'lt invalid with string' => [
                'data' => [
                    'field-a' => '2',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->lt('field-b'),
                ],
                'fails' => true,
            ],
            'lte valid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->lte(0.5),
                'fails' => false,
            ],
            'lte invalid with float' => [
                'data' => 0.6,
                'rules' => fn() => RuleSet::create()->numeric()->lte(0.5),
                'fails' => true,
            ],
            'lte valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->lte(100),
                'fails' => false,
            ],
            'lte invalid with number' => [
                'data' => 101,
                'rules' => fn() => RuleSet::create()->numeric()->lte(100),
                'fails' => true,
            ],
            'lte valid with BigNumber' => [
                'data' => '9223372036854775810',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->lte(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'lte invalid with BigNumber' => [
                'data' => '9223372036854775811',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->lte(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'lte valid with string' => [
                'data' => [
                    'field-a' => '1',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->lte('field-b'),
                ],
                'fails' => false,
            ],
            'lte invalid with string' => [
                'data' => [
                    'field-a' => '2',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->lte('field-b'),
                ],
                'fails' => true,
            ],
            'mac address valid' => [
                'data' => '00-11-22-33-44-55',
                'rules' => fn() => RuleSet::create()->macAddress(),
                'fails' => false,
            ],
            'mac address invalid' => [
                'data' => 'a',
                'rules' => fn() => RuleSet::create()->macAddress(),
                'fails' => true,
            ],
            'max valid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->max(0.5),
                'fails' => false,
            ],
            'max invalid with float' => [
                'data' => 0.6,
                'rules' => fn() => RuleSet::create()->numeric()->max(0.5),
                'fails' => true,
            ],
            'max valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->max(100),
                'fails' => false,
            ],
            'max invalid with number' => [
                'data' => 101,
                'rules' => fn() => RuleSet::create()->numeric()->max(100),
                'fails' => true,
            ],
            'max valid with string' => [
                'data' => 24,
                'rules' => fn() => RuleSet::create()->numeric()->max('25'),
                'fails' => false,
            ],
            'max invalid with string' => [
                'data' => 76,
                'rules' => fn() => RuleSet::create()->numeric()->max('75'),
                'fails' => true,
            ],
            'max valid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->max(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'max invalid with BigNumber' => [
                'data' => '9223372036854775811',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->max(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'max valid with string length' => [
                'data' => str_repeat('.', 10),
                'rules' => fn() => RuleSet::create()->max(10),
                'fails' => false,
            ],
            'max invalid with string length' => [
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
            'maxDigits valid' => [
                'data' => 123,
                'rules' => fn() => RuleSet::create()->maxDigits(3),
                'fails' => false,
            ],
            'maxDigits invalid' => [
                'data' => 123,
                'rules' => fn() => RuleSet::create()->maxDigits(2),
                'fails' => true,
            ],
            'mimes valid' => [
                'data' => fn() => $this->mockFile('/code/document.odf'),
                'rules' => fn() => RuleSet::create()->mimes('pdf', 'odf'),
                'fails' => false,
            ],
            'mimes invalid' => [
                'data' => fn() => $this->mockFile('/code/document.ppt'),
                'rules' => fn() => RuleSet::create()->mimes('pdf', 'odf'),
                'fails' => true,
            ],
            'mimetypes valid' => [
                'data' => fn() => $this->mockFile('/code/document.pdf', 'application/pdf'),
                'rules' => fn() => RuleSet::create()->mimetypes('image/jpg', 'application/pdf'),
                'fails' => false,
            ],
            'mimetypes invalid' => [
                'data' => fn() => $this->mockFile('/code/image.jpg', 'image/jpg'),
                'rules' => fn() => RuleSet::create()->mimetypes('image/gif', 'application/pdf'),
                'fails' => true,
            ],
            'min valid with float' => [
                'data' => 0.5,
                'rules' => fn() => RuleSet::create()->numeric()->min(0.5),
                'fails' => false,
            ],
            'min invalid with float' => [
                'data' => 0.1,
                'rules' => fn() => RuleSet::create()->numeric()->min(0.5),
                'fails' => true,
            ],
            'min valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->min(100),
                'fails' => false,
            ],
            'min invalid with number' => [
                'data' => 99,
                'rules' => fn() => RuleSet::create()->numeric()->min(100),
                'fails' => true,
            ],
            'min valid with string' => [
                'data' => 76,
                'rules' => fn() => RuleSet::create()->numeric()->min('75'),
                'fails' => false,
            ],
            'min invalid with string' => [
                'data' => 24,
                'rules' => fn() => RuleSet::create()->numeric()->min('25'),
                'fails' => true,
            ],
            'min valid with BigNumber' => [
                'data' => '9223372036854775811',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->min(BigNumber::of('9223372036854775810')),
                'fails' => false,
            ],
            'min invalid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->min(BigNumber::of('9223372036854775810')),
                'fails' => true,
            ],
            'min valid with string length' => [
                'data' => str_repeat('.', 11),
                'rules' => fn() => RuleSet::create()->min(10),
                'fails' => false,
            ],
            'min invalid with string length' => [
                'data' => str_repeat('.', 9),
                'rules' => fn() => RuleSet::create()->min(10),
                'fails' => true,
            ],
            'min valid with array' => [
                'data' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => RuleSet::create()->min(3),
                'fails' => false,
            ],
            'min invalid with array' => [
                'data' => [
                    'field' => ['a', 'b', 'c'],
                ],
                'rules' => fn() => RuleSet::create()->min(4),
                'fails' => true,
            ],
            'minDigits valid' => [
                'data' => 123,
                'rules' => fn() => RuleSet::create()->minDigits(3),
                'fails' => false,
            ],
            'minDigits invalid' => [
                'data' => 12,
                'rules' => fn() => RuleSet::create()->minDigits(3),
                'fails' => true,
            ],
            'missing valid' => [
                'data' => [
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missing(),
                ],
                'fails' => false,
            ],
            'missing invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missing(),
                ],
                'fails' => true,
            ],
            'missingIf valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'mock',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingIf('field-b', 'test'),
                ],
                'fails' => false,
            ],
            'missingIf invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'test',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingIf('field-b', 'test'),
                ],
                'fails' => true,
            ],
            'missingUnless valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'mock',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingUnless('field-b', 'mock'),
                ],
                'fails' => false,
            ],
            'missingUnless invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'test',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingUnless('field-b', 'mock'),
                ],
                'fails' => true,
            ],
            'missingWith valid' => [
                'data' => [
                    'field-c' => 'mock',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingWith('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'missingWith invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-c' => 'test',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingWith('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'missingWithAll valid' => [
                'data' => [
                    'field-a' => 'mock',
                    'field-c' => 'mock',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingWithAll('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'missingWithAll invalid' => [
                'data' => [
                    'field-a' => 'mock',
                    'field-b' => 'mock',
                    'field-c' => 'mock',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->missingWithAll('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'multipleOf valid' => [
                'data' => 9.9,
                'rules' => fn() => RuleSet::create()->multipleOf(3.3),
                'fails' => false,
            ],
            'multipleOf invalid' => [
                'data' => 9,
                'rules' => fn() => RuleSet::create()->multipleOf(2),
                'fails' => true,
            ],
            'notIn valid' => [
                'data' => [
                    'field-a' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->notIn(['a', 'b', 'c']),
                ],
                'fails' => false,
            ],
            'notIn invalid' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->notIn(['a', 'b', 'c']),
                ],
                'fails' => true,
            ],
            'notRegex valid' => [
                'data' => 'value-1',
                'rules' => fn() => RuleSet::create()->notRegex('/[a-z]+$/'),
                'fails' => false,
            ],
            'notRegex invalid' => [
                'data' => 'value-a',
                'rules' => fn() => RuleSet::create()->notRegex('/[a-z]+$/'),
                'fails' => true,
            ],
            'nullable valid' => [
                'data' => null,
                'rules' => fn() => RuleSet::create()->nullable()->string(),
                'fails' => false,
            ],
            'nullable invalid' => [
                'data' => 1,
                'rules' => fn() => RuleSet::create()->nullable()->string(),
                'fails' => true,
            ],
            'numeric valid' => [
                'data' => '1.25',
                'rules' => fn() => RuleSet::create()->numeric(),
                'fails' => false,
            ],
            'numeric invalid' => [
                'data' => 'a',
                'rules' => fn() => RuleSet::create()->numeric(),
                'fails' => true,
            ],
            'password using default valid' => [
                'data' => 'pass1!',
                'rules' => function () {
                    Password::defaults(Password::min(6)->letters()->numbers()->symbols());

                    return RuleSet::create()->password();
                },
                'fails' => false,
            ],
            'password using default invalid' => [
                'data' => 'passwordWithoutNumbersOrSymbols',
                'rules' => function () {
                    Password::defaults(Password::min(10)->letters()->numbers()->symbols());

                    return RuleSet::create()->password();
                },
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must contain at least one symbol.',
                        'The field field must contain at least one number.',
                    ],
                ],
            ],
            'password overriding default valid' => [
                'data' => 'password',
                'rules' => function () {
                    // Set a default password rule set.
                    Password::defaults(Password::min(10)->letters()->numbers()->symbols());

                    // Make our rule less strict to pass.
                    return RuleSet::create()->password(8);
                },
                'fails' => false,
            ],
            'password overriding default invalid' => [
                'data' => 'password',
                'rules' => function () {
                    // Set a default password rule set.
                    Password::defaults(Password::min(9)->letters()->numbers());

                    // Make our rule more strict to fail and ensure both our error and the default were included.
                    return RuleSet::create()->password(null, fn(Password $rule) => $rule->symbols());
                },
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must be at least 9 characters.',
                        'The field field must contain at least one symbol.',
                        'The field field must contain at least one number.',
                    ],
                ],
            ],
            'password valid' => [
                'data' => 'password',
                'rules' => fn() => RuleSet::create()->password(),
                'fails' => false,
            ],
            'password invalid' => [
                'data' => 'pass',
                'rules' => fn() => RuleSet::create()->password(),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must be at least 8 characters.',
                    ],
                ],
            ],
            'present valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->present(),
                ],
                'fails' => false,
            ],
            'present invalid' => [
                'data' => [
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->present(),
                ],
                'fails' => true,
            ],
            'prohibited valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibited(),
                ],
                'fails' => false,
            ],
            'prohibited invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibited(),
                ],
                'fails' => true,
            ],
            'prohibitedIf valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedIf(true),
                ],
                'fails' => false,
            ],
            'prohibitedIf invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedIf(true),
                ],
                'fails' => true,
            ],
            'prohibitedIfValue valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedIfValue('field-b', 'a', 'b', 'c'),
                ],
                'fails' => false,
            ],
            'prohibitedIfValue invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedIfValue('field-b', 'a', 'b', 'c'),
                ],
                'fails' => true,
            ],
            'prohibitedUnless valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedUnless('field-b', 'a', 'b', 'c'),
                ],
                'fails' => false,
            ],
            'prohibitedUnless invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibitedUnless('field-b', 'a', 'b', 'c'),
                ],
                'fails' => true,
            ],
            'prohibits valid not defined' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibits('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'prohibits valid defined without prohibited' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibits('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'prohibits invalid defined with prohibited' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibits('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'prohibits invalid defined with secondary prohibited' => [
                'data' => [
                    'field-a' => 'a',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->prohibits('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'concat adds to set' => [
                'data' => 11,
                'rules' => fn() => RuleSet::create()->string()->concat('max:1'),
                'fails' => true,
                'errors' => [
                    'field' => [
                        'The field field must be a string.',
                        'The field field must not be greater than 1 characters.',
                    ],
                ],
            ],
            'regex valid' => [
                'data' => 'value-a',
                'rules' => fn() => RuleSet::create()->regex('/[a-z]+$/'),
                'fails' => false,
            ],
            'regex invalid' => [
                'data' => 'value-1',
                'rules' => fn() => RuleSet::create()->regex('/[a-z]+$/'),
                'fails' => true,
            ],
            'required valid' => [
                'data' => 'value',
                'rules' => fn() => RuleSet::create()->required(),
                'fails' => false,
            ],
            'required invalid' => [
                'data' => '',
                'rules' => fn() => RuleSet::create()->required(),
                'fails' => true,
            ],
            'requiredArrayKeys valid' => [
                'data' => ['field' => ['a' => '1', 'b' => '2', 'c' => '3']],
                'rules' => fn() => ['field' => RuleSet::create()->requiredArrayKeys('a', 'b', 'c')],
                'fails' => false,
            ],
            'requiredArrayKeys invalid' => [
                'data' => ['field' => ['a' => '1', 'c' => '3']],
                'rules' => fn() => ['field' => RuleSet::create()->requiredArrayKeys('a', 'b', 'c')],
                'fails' => true,
            ],
            'requiredIf bool valid' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIf(true),
                ],
                'fails' => false,
            ],
            'requiredIf bool invalid' => [
                'data' => [
                    'field-a' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIf(true),
                ],
                'fails' => true,
            ],
            'requiredIf callback valid' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIf(fn() => true),
                ],
                'fails' => false,
            ],
            'requiredIf callback invalid' => [
                'data' => [
                    'field-a' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIf(fn() => true),
                ],
                'fails' => true,
            ],
            'requiredIfAccepted valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIfAccepted('field-b'),
                ],
                'fails' => false,
            ],
            'requiredIfAccepted invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '1',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIfAccepted('field-b'),
                ],
                'fails' => true,
            ],
            'requiredIfValue valid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIfValue('field-b', 'a', 'b', 'c'),
                ],
                'fails' => false,
            ],
            'requiredIfValue invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredIfValue('field-b', 'a', 'b', 'c'),
                ],
                'fails' => true,
            ],
            'requiredUnless valid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredUnless('field-b', 'a', 'b', 'c'),
                ],
                'fails' => false,
            ],
            'requiredUnless invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'd',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredUnless('field-b', 'a', 'b', 'c'),
                ],
                'fails' => true,
            ],
            'requiredWith valid - required' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWith('field-b'),
                ],
                'fails' => false,
            ],
            'requiredWith valid - not required' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWith('field-b'),
                ],
                'fails' => false,
            ],
            'requiredWith invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWith('field-b'),
                ],
                'fails' => true,
            ],
            'requiredWithAll valid - all' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithAll('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'requiredWithAll valid - not all' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                    'field-c' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithAll('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'requiredWithAll invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithAll('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'requiredWithout valid - required' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithout('field-b'),
                ],
                'fails' => false,
            ],
            'requiredWithout valid - not required' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithout('field-b'),
                ],
                'fails' => false,
            ],
            'requiredWithout invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithout('field-b'),
                ],
                'fails' => true,
            ],
            'requiredWithoutAll valid - all' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => '',
                    'field-c' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithoutAll('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'requiredWithoutAll valid - not all' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                    'field-c' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithoutAll('field-b', 'field-c'),
                ],
                'fails' => false,
            ],
            'requiredWithoutAll invalid' => [
                'data' => [
                    'field-a' => '',
                    'field-b' => '',
                    'field-c' => '',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->requiredWithoutAll('field-b', 'field-c'),
                ],
                'fails' => true,
            ],
            'same valid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->same('field-b'),
                ],
                'fails' => false,
            ],
            'same invalid' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->same('field-b'),
                ],
                'fails' => true,
            ],
            'size valid with float' => [
                'data' => 1.0,
                'rules' => fn() => RuleSet::create()->numeric()->size(1.0),
                'fails' => false,
            ],
            'size invalid with float' => [
                'data' => 1.0,
                'rules' => fn() => RuleSet::create()->numeric()->size(1.1),
                'fails' => true,
            ],
            'size valid with number' => [
                'data' => 100,
                'rules' => fn() => RuleSet::create()->numeric()->size(100),
                'fails' => false,
            ],
            'size invalid with number' => [
                'data' => 101,
                'rules' => fn() => RuleSet::create()->numeric()->size(100),
                'fails' => true,
            ],
            'size valid with string' => [
                'data' => 75,
                'rules' => fn() => RuleSet::create()->numeric()->size('75'),
                'fails' => false,
            ],
            'size invalid with string' => [
                'data' => 76,
                'rules' => fn() => RuleSet::create()->numeric()->size('75'),
                'fails' => true,
            ],
            'size valid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->size(BigNumber::of('9223372036854775809')),
                'fails' => false,
            ],
            'size invalid with BigNumber' => [
                'data' => '9223372036854775809',
                'rules' => fn() => RuleSet::create()->numeric()
                    ->size(BigNumber::of('9223372036854775808')),
                'fails' => true,
            ],
            'size valid with string length' => [
                'data' => 'str',
                'rules' => fn() => RuleSet::create()->size(3),
                'fails' => false,
            ],
            'size invalid with string length' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->size(3),
                'fails' => true,
            ],
            'sometimes valid' => [
                'data' => [
                    'field-b' => 2,
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->sometimes()->string(),
                ],
                'fails' => false,
            ],
            'sometimes invalid' => [
                'data' => [
                    'field-a' => 1,
                    'field-b' => 2,
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->sometimes()->string(),
                ],
                'fails' => true,
            ],
            'startsWith valid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->startsWith('s'),
                'fails' => false,
            ],
            'startsWith invalid' => [
                'data' => 'string',
                'rules' => fn() => RuleSet::create()->startsWith('a'),
                'fails' => true,
            ],
            'startsWith any valid' => [
                'data' => 'c-string',
                'rules' => fn() => RuleSet::create()->startsWith('a', 'b', 'c'),
                'fails' => false,
            ],
            'startsWith any invalid' => [
                'data' => 'd-string',
                'rules' => fn() => RuleSet::create()->startsWith('a', 'b', 'c'),
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
            'timezone valid' => [
                'data' => 'America/New_York',
                'rules' => fn() => RuleSet::create()->timezone(),
                'fails' => false,
            ],
            'timezone invalid' => [
                'data' => 'not a timezone',
                'rules' => fn() => RuleSet::create()->timezone(),
                'fails' => true,
            ],
            'ulid valid' => [
                'data' => Str::ulid()->toBase32(),
                'rules' => fn() => RuleSet::create()->ulid(),
                'fails' => false,
            ],
            'ulid invalid' => [
                'data' => Str::uuid()->toString(),
                'rules' => fn() => RuleSet::create()->ulid(),
                'fails' => true,
            ],
            'uppercase valid' => [
                'data' => 'UPPERCASE',
                'rules' => fn() => RuleSet::create()->uppercase(),
                'fails' => false,
            ],
            'uppercase invalid' => [
                'data' => 'Uppercase',
                'rules' => fn() => RuleSet::create()->uppercase(),
                'fails' => true,
            ],
            'url valid' => [
                'data' => 'https://example.com',
                'rules' => fn() => RuleSet::create()->url(),
                'fails' => false,
            ],
            'url invalid' => [
                'data' => 'not a url',
                'rules' => fn() => RuleSet::create()->url(),
                'fails' => true,
            ],
            'uuid valid' => [
                'data' => '4b74310e-3253-49c7-965b-8002ea52432d',
                'rules' => fn() => RuleSet::create()->uuid(),
                'fails' => false,
            ],
            'uuid invalid' => [
                'data' => '123456',
                'rules' => fn() => RuleSet::create()->uuid(),
                'fails' => true,
            ],
            'when valid' => [
                'data' => 9,
                'rules' => fn() => RuleSet::create()->when(fn() => false, RuleSet::create()->min(10)),
                'fails' => false,
            ],
            'when invalid' => [
                'data' => 9,
                'rules' => fn() => RuleSet::create()->when(fn() => true, RuleSet::create()->min(10)),
                'fails' => true,
            ],
            'when invalid fallback' => [
                'data' => 9,
                'rules' => fn() => RuleSet::create()->when(
                    fn() => false,
                    RuleSet::create()->numeric(),
                    RuleSet::create()->string()
                ),
                'fails' => true,
            ],
        ];
    }

    public function excludeProvider(): array
    {
        return [
            'exclude' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->exclude(),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-b' => 'b',
                ],
            ],
            'excludeIf match' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeIf(true),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-b' => 'b',
                ],
            ],
            'excludeIf not matched' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeIf(false),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
            ],
            'excludeIfValue match' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeIfValue('field-b', 'b'),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-b' => 'b',
                ],
            ],
            'excludeIfValue not matched' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeIfValue('field-b', 'b'),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
            ],
            'excludeUnless match' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeUnless('field-b', 'b'),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                ],
            ],
            'excludeUnless not matched' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeUnless('field-b', 'b'),
                    'field-b' => RuleSet::create(),
                ],
                'expected' => [
                    'field-b' => 'c',
                ],
            ],
            'excludeWith match' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeWith('field-b'),
                    'field-b' => RuleSet::create(),
                    'field-c' => RuleSet::create(),
                ],
                'expected' => [
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
            ],
            'excludeWith not matched' => [
                'data' => [
                    'field-a' => 'a',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeWith('field-b'),
                    'field-b' => RuleSet::create(),
                    'field-c' => RuleSet::create(),
                ],
                'expected' => [
                    'field-a' => 'a',
                    'field-c' => 'c',
                ],
            ],
            'excludeWithout match' => [
                'data' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeWithout('field-b'),
                    'field-b' => RuleSet::create(),
                    'field-c' => RuleSet::create(),
                ],
                'expected' => [
                    'field-a' => 'a',
                    'field-b' => 'b',
                    'field-c' => 'c',
                ],
            ],
            'excludeWithout not matched' => [
                'data' => [
                    'field-a' => 'a',
                    'field-c' => 'c',
                ],
                'rules' => fn() => [
                    'field-a' => RuleSet::create()->excludeWithout('field-b'),
                    'field-b' => RuleSet::create(),
                    'field-c' => RuleSet::create(),
                ],
                'expected' => [
                    'field-c' => 'c',
                ],
            ],
        ];
    }

    public function requireIfProvider(): array
    {
        return [
            'requiredIfAny required with no data' => [
                'data' => '',
                'rule' => fn() => RuleSet::create()->requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => true,
            ],
            'requiredIfAny required with data (callback)' => [
                'data' => 'not empty',
                'rule' => fn() => RuleSet::create()->requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAny required with data (bool)' => [
                'data' => 'not empty',
                'rule' => fn() => RuleSet::create()->requiredIfAny(
                    Rule::requiredIf(false),
                    Rule::requiredIf(true),
                ),
                'fails' => false,
            ],
            'requiredIfAny not required' => [
                'data' => '',
                'rule' => fn() => RuleSet::create()->requiredIfAny(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => false),
                ),
                'fails' => false,
            ],
            'requiredIfAll required with no data' => [
                'data' => '',
                'rule' => fn() => RuleSet::create()->requiredIfAll(
                    Rule::requiredIf(fn() => true),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => true,
            ],
            'requiredIfAll required with data (callback)' => [
                'data' => 'not empty',
                'rule' => fn() => RuleSet::create()->requiredIfAll(
                    Rule::requiredIf(fn() => true),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAll required with data (bool)' => [
                'data' => 'not empty',
                'rule' => fn() => RuleSet::create()->requiredIfAll(
                    Rule::requiredIf(true),
                    Rule::requiredIf(true),
                ),
                'fails' => false,
            ],
            'requiredIfAll not required (mismatch)' => [
                'data' => '',
                'rule' => fn() => RuleSet::create()->requiredIfAll(
                    Rule::requiredIf(fn() => false),
                    Rule::requiredIf(fn() => true),
                ),
                'fails' => false,
            ],
            'requiredIfAll not required (false)' => [
                'data' => '',
                'rule' => fn() => RuleSet::create()->requiredIfAll(
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

    private function mockFile(string $path, ?string $mimeType = null): File
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($mimeType) {
            // We need to start a new instance in case a guesser was already created for a previous test.
            $finder = new MimeTypes();

            $finder->registerGuesser(new class($mimeType) implements MimeTypeGuesserInterface {
                private string $mimeType;

                public function __construct(string $mimeType)
                {
                    $this->mimeType = $mimeType;
                }

                public function isGuesserSupported(): bool
                {
                    return true;
                }

                public function guessMimeType(string $path): ?string
                {
                    return $this->mimeType;
                }
            });

            MimeTypes::setDefault($finder);
        }

        return new class($path, $extension) extends File {
            private string $extension;

            public function __construct(string $path, string $extension)
            {
                parent::__construct($path, false);

                $this->extension = $extension;
            }

            public function guessExtension(): string
            {
                return $this->extension;
            }
        };
    }
}
