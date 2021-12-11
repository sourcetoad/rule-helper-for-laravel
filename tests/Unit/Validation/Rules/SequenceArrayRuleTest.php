<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Tests\Unit\Validation\Rules;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Sourcetoad\RuleHelper\Tests\TestCase;
use Sourcetoad\RuleHelper\Validation\Rules\SequentialValuesRule;

/**
 * @covers \Sourcetoad\RuleHelper\ServiceProvider
 * @covers \Sourcetoad\RuleHelper\Validation\Rules\SequentialValuesRule
 * @covers \Sourcetoad\RuleHelper\Validation\Rules\Comparators\DateComparator
 * @covers \Sourcetoad\RuleHelper\Validation\Rules\Comparators\NumericComparator
 * @covers \Sourcetoad\RuleHelper\Validation\Rules\Comparators\StringComparator
 */
class SequenceArrayRuleTest extends TestCase
{
    /** @dataProvider payloadProvider */
    public function testGeneratesMessagesBasedOnTranslations(array $data, \Closure $rules, array $messages): void
    {
        // Arrange
        $validator = Validator::make($data, $rules->call($this));

        // Act
        $validatorFailed = $validator->fails();

        // Assert
        $this->assertEquals(true, $validatorFailed, 'Failed asserting that validator failed.');
        $this->assertEquals($messages, $validator->errors()->toArray());
    }

    private function setupTranslatorMock(): void
    {
        $loader = new ArrayLoader();
        $loader->addMessages('en-US', 'validation', [
            'sequential_values' => [
                'not_sequential' => 'The :attribute field should come before :previous.',
                'not_array' => 'The :attribute field is not an array.',
            ],
        ]);

        $translator = new class($loader, 'en-US') extends Translator {
        };

        Lang::swap($translator);
    }

    public function payloadProvider(): array
    {
        return [
            'not an array no translation' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => fn() => [
                    'field-a' => [new SequentialValuesRule()],
                ],
                'messages' => ['field-a' => ['sequential_values.not_array']],
            ],
            'not an array with translation' => [
                'data' => [
                    'field-a' => 'a',
                ],
                'rules' => function () {
                    $this->setupTranslatorMock();
                    return [
                        'field-a' => [new SequentialValuesRule()],
                    ];
                },
                'messages' => ['field-a' => ['The field-a field is not an array.']],
            ],
            'not in sequence' => [
                'data' => [
                    'field-a' => [
                        'a', // field-a.0
                        'e', // field-a.1
                        'd', // field-a.2
                        'f', // field-a.3
                        'b', // field-a.4
                    ],
                ],
                'rules' => function () {
                    $this->setupTranslatorMock();
                    return [
                        'field-a.*' => [new SequentialValuesRule()],
                    ];
                },
                'messages' => [
                    'field-a.2' => ['The field-a.2 field should come before field-a.1.'],
                    'field-a.4' => ['The field-a.4 field should come before field-a.1.'],
                ],
            ],
        ];
    }
}
