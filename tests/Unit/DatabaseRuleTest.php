<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as LaravelRule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use PHPUnit\Framework\Attributes\DataProvider;
use Sourcetoad\RuleHelper\RuleSet;
use Sourcetoad\RuleHelper\Tests\TestCase;

class DatabaseRuleTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
    }

    #[DataProvider('databaseSetupProvider')]
    public function testDatabaseRules(Closure $createData, Closure $createRules, bool $fails): void
    {
        // Arrange
        $data = $createData->call($this);
        $rules = $createRules->call($this);

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
    }

    public static function databaseSetupProvider(): array
    {
        return [
            'does not exist without column' => [
                'createData' => fn() => ['email' => $this->faker->email],
                'createRules' => fn() => [
                    'email' => RuleSet::create()->exists('users'),
                ],
                'fails' => true,
            ],
            'exists without column' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['email' => $email];
                },
                'createRules' => fn() => [
                    'email' => RuleSet::create()->exists('users'),
                ],
                'fails' => false,
            ],
            'does not exist with column' => [
                'createData' => fn() => ['value' => $this->faker->email],
                'createRules' => fn() => [
                    'value' => RuleSet::create()->exists('users', 'email'),
                ],
                'fails' => true,
            ],
            'exists with column' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->exists('users', 'email'),
                ],
                'fails' => false,
            ],
            'does not exist with modifier' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->exists(
                        'users',
                        'email',
                        fn(Exists $exists) => $exists->where('name', 'test')
                    ),
                ],
                'fails' => true,
            ],
            'exists with modifier' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => 'test',
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->exists(
                        'users',
                        'email',
                        fn(Exists $exists) => $exists->where('name', 'test')
                    ),
                ],
                'fails' => false,
            ],
            'not unique without column' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['email' => $email];
                },
                'createRules' => fn() => [
                    'email' => RuleSet::create()->unique('users'),
                ],
                'fails' => true,
            ],
            'unique without column' => [
                'createData' => fn() => ['email' => $this->faker->email],
                'createRules' => fn() => [
                    'email' => RuleSet::create()->unique('users'),
                ],
                'fails' => false,
            ],
            'not unique with column' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique('users', 'email'),
                ],
                'fails' => true,
            ],
            'unique with column' => [
                'createData' => fn() => ['value' => $this->faker->email],
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique('users', 'email'),
                ],
                'fails' => false,
            ],
            'not unique with modifier' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => 'test',
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique(
                        'users',
                        'email',
                        fn(Unique $rule) => $rule->where('name', 'test')
                    ),
                ],
                'fails' => true,
            ],
            'not unique with modifier that does not return' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => 'modified',
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique(
                        'users',
                        'email',
                        function (Unique $rule) {
                            $rule->where('name', 'modified');
                        }
                    ),
                ],
                'fails' => true,
            ],
            'not unique with modifier that overwrites' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => 'overwritten',
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique(
                        'invalid_users',
                        'email',
                        function () {
                            return LaravelRule::unique('users', 'email')
                                ->where('name', 'overwritten');
                        }
                    ),
                ],
                'fails' => true,
            ],
            'unique with modifier' => [
                'createData' => function () {
                    $email = $this->faker->email;
                    DB::table('users')->insert([
                        'name' => $this->faker->name,
                        'email' => $email,
                        'password' => $this->faker->password,
                    ]);

                    return ['value' => $email];
                },
                'createRules' => fn() => [
                    'value' => RuleSet::create()->unique(
                        'users',
                        'email',
                        fn(Unique $rule) => $rule->where('name', 'test')
                    ),
                ],
                'fails' => false,
            ],
        ];
    }
}
