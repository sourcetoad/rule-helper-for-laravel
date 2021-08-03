<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Sourcetoad\RuleHelper\Rule;
use Sourcetoad\RuleHelper\Tests\TestCase;

class RuleTest extends TestCase
{
    /**
     * @dataProvider requireIfProvider
     */
    public function testRequiredIfExtensions(array $data, array $rules, bool $fails): void
    {
        // Arrange
        $validator = Validator::make($data, $rules);

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals($fails, $validatorFailed);
    }

    public function requireIfProvider(): array
    {
        return [
            'requiredIfAny required with no data' => [
                'data' => [
                    'field' => '',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAny(
                        Rule::requiredIf(fn() => false),
                        Rule::requiredIf(fn() => true),
                    ),
                ],
                'fails' => true,
            ],
            'requiredIfAny required with data (callback)' => [
                'data' => [
                    'field' => 'data',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAny(
                        Rule::requiredIf(fn() => false),
                        Rule::requiredIf(fn() => true),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAny required with data (bool)' => [
                'data' => [
                    'field' => 'data',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAny(
                        Rule::requiredIf(false),
                        Rule::requiredIf(true),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAny not required' => [
                'data' => [
                    'field' => '',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAny(
                        Rule::requiredIf(fn() => false),
                        Rule::requiredIf(fn() => false),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAll required with no data' => [
                'data' => [
                    'field' => '',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAll(
                        Rule::requiredIf(fn() => true),
                        Rule::requiredIf(fn() => true),
                    ),
                ],
                'fails' => true,
            ],
            'requiredIfAll required with data (callback)' => [
                'data' => [
                    'field' => 'data',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAll(
                        Rule::requiredIf(fn() => true),
                        Rule::requiredIf(fn() => true),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAll required with data (bool)' => [
                'data' => [
                    'field' => 'data',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAll(
                        Rule::requiredIf(true),
                        Rule::requiredIf(true),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAll not required (mismatch)' => [
                'data' => [
                    'field' => '',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAll(
                        Rule::requiredIf(fn() => false),
                        Rule::requiredIf(fn() => true),
                    ),
                ],
                'fails' => false,
            ],
            'requiredIfAll not required (false)' => [
                'data' => [
                    'field' => '',
                ],
                'rules' => [
                    'field' => Rule::requiredIfAll(
                        Rule::requiredIf(fn() => false),
                        Rule::requiredIf(fn() => false),
                    ),
                ],
                'fails' => false,
            ],
        ];
    }
}
