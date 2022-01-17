<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
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

    /**
     * @dataProvider databaseSetupProvider
     */
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

    public function databaseSetupProvider(): array
    {
        return [
            'does not exist without column' => [
                'createData' => fn() => ['email' => $this->faker->email],
                'rule' => fn() => [
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
                'rule' => fn() => [
                    'email' => RuleSet::create()->exists('users'),
                ],
                'fails' => false,
            ],
            'does not exist with column' => [
                'createData' => fn() => ['value' => $this->faker->email],
                'rule' => fn() => [
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
                'rule' => fn() => [
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
                'rule' => fn() => [
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
                'rule' => fn() => [
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
                'rule' => fn() => [
                    'email' => RuleSet::create()->unique('users'),
                ],
                'fails' => true,
            ],
            'unique without column' => [
                'createData' => fn() => ['email' => $this->faker->email],
                'rule' => fn() => [
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
                'rule' => fn() => [
                    'value' => RuleSet::create()->unique('users', 'email'),
                ],
                'fails' => true,
            ],
            'unique with column' => [
                'createData' => fn() => ['value' => $this->faker->email],
                'rule' => fn() => [
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
                'rule' => fn() => [
                    'value' => RuleSet::create()->unique(
                        'users',
                        'email',
                        fn(Unique $rule) => $rule->where('name', 'test')
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
                'rule' => fn() => [
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
