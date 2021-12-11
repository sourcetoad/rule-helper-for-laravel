<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Validation\Rules;

trait UsesAttributeIndexes
{
    private string $attributeIndexMatch = '/\.(\d+)(\.[^.]+)?$/';

    protected function getAttributeAtIndex(string $attribute, int $index): string
    {
        return preg_replace($this->attributeIndexMatch, '.'.preg_quote((string)$index, '/').'${2}', $attribute);
    }

    protected function getIndexFromAttribute(string $attribute): ?int
    {
        if (!preg_match($this->attributeIndexMatch, $attribute, $currentIndexMatch)) {
            return null;
        }

        return (int)$currentIndexMatch[1];
    }

    protected function getPreviousAttributes(string $attribute): ?array
    {
        $currentIndex = $this->getIndexFromAttribute($attribute);
        if ($currentIndex === null) {
            return null;
        }

        $previousAttributeNames = [];

        foreach (array_keys(array_fill(0, $currentIndex, '')) as $previousIndex) {
            $previousAttributeNames[] = $this->getAttributeAtIndex($attribute, $previousIndex);
        }

        return $previousAttributeNames;
    }
}
