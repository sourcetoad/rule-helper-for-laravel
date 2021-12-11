<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Validator;
use Sourcetoad\RuleHelper\Validation\Rules\Comparators\Comparator;
use Sourcetoad\RuleHelper\Validation\Rules\Comparators\DateComparator;
use Sourcetoad\RuleHelper\Validation\Rules\Comparators\NumericComparator;
use Sourcetoad\RuleHelper\Validation\Rules\Comparators\StringComparator;

class SequentialValuesRule implements Rule, DataAwareRule, ValidatorAwareRule
{
    private bool $allowEqual = false;
    private string $lastMessage = 'sequential_values.not_checked';
    private array $comparators = [
        DateComparator::class,
        NumericComparator::class,
        StringComparator::class,
    ];
    private array $data;
    private Validator $validator;

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setValidator($validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    public function setAllowEqual(bool $allowEqual): self
    {
        $this->allowEqual = $allowEqual;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        $previousAttributes = $this->getPreviousAttributes($attribute);

        // If we fail to obtain a previous attribute array, the attribute name was not in an expected format leaving us
        // nothing to compare against.
        if ($previousAttributes === null) {
            $this->lastMessage = $this->generateMessage('sequential_values.not_array', ['attribute' => $attribute]);
            return false;
        }

        // If we have no previous attributes we are at the first index and should always pass.
        if (count($previousAttributes) < 1) {
            return true;
        }

        $comparator = $this->determineComparator($attribute);

        foreach ($previousAttributes as $previousAttribute) {
            $previousValue = Arr::get($this->data, $previousAttribute);

            if ($comparator->compare($value, $previousValue) < ($this->allowEqual ? 0 : 1)) {
                $this->lastMessage = $this->generateMessage('sequential_values.not_sequential', [
                    'attribute' => $attribute,
                    'previous' => $previousAttribute,
                ]);
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->lastMessage;
    }

    private function determineComparator(string $attribute): Comparator
    {
        $attributeRules = Arr::get($this->validator->getRules(), $attribute, []);

        foreach ($this->comparators as $comparatorClass) {
            $comparator = resolve($comparatorClass);
            if (!($comparator instanceof Comparator)) {
                throw new \RuntimeException('Comparator does not implement '.Comparator::class);
            }

            if ($comparator->canHandle($attributeRules)) {
                return $comparator;
            }
        }

        throw new \RuntimeException('No '.Comparator::class.' found to handle '.$attribute);
    }

    private function generateMessage(string $key, array $replace): string
    {
        if (Lang::has("validation.$key")) {
            return Lang::get("validation.$key", $replace);
        }

        return $key;
    }

    private function getPreviousAttributes(string $attribute): ?array
    {
        $attributeIndexMatch = '/\.(\d+)(\.[^.]+)?$/';
        if (!preg_match($attributeIndexMatch, $attribute, $currentIndexMatch)) {
            return null;
        }

        $currentIndex = (int)$currentIndexMatch[1];
        $previousAttributeNames = [];

        foreach (array_keys(array_fill(0, $currentIndex, '')) as $previousIndex) {
            $previousAttributeNames[] = preg_replace(
                $attributeIndexMatch,
                '.'.preg_quote((string)$previousIndex, '/').'${2}',
                $attribute
            );
        }

        return $previousAttributeNames;
    }
}
