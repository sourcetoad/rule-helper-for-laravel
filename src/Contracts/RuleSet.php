<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Contracts;

use Illuminate\Validation\Rules\RequiredIf;

interface RuleSet
{
    public static function create(array $rules = []): self;
    public function push(...$rule): self;
    public function rule($rule): self;
    public function accepted(): self;
    public function acceptedIf(string $anotherField, string ...$value): self;
    public function activeUrl(): self;
    public function after($date): self;
    public function afterOrEqual($date): self;
    public function alpha(): self;
    public function alphaDash(): self;
    public function alphaNum(): self;
    public function array(string ...$requiredKey): self;
    public function bail(): self;
    public function before($date): self;
    public function beforeOrEqual($date): self;
    public function between(int $min, int $max): self;
    public function boolean(): self;
    public function confirmed(): self;
    public function currentPassword(?string $authenticationGuard = null): self;
    public function date(): self;
    public function dateEquals($date): self;
    public function dateFormat(string $dateFormat): self;
    public function different(string $field): self;
    public function digits(int $count): self;
    public function digitsBetween(int $min, int $max): self;
    public function distinct(bool $strict = false, $ignoreCase = false): self;
    public function email(string ...$validator): self;
    public function endsWith(string ...$value): self;
    public function excludeIf(string $anotherField, ?string $value): self;
    public function excludeUnless(string $anotherField, ?string $value): self;
    public function excludeWithout(string $anotherField): self;
    public function file(): self;
    public function filled(): self;
    public function gt(string $field): self;
    public function gte(string $field): self;
    public function image(): self;
    public function inArray(string $anotherField): self;
    public function integer(): self;
    public function ip(): self;
    public function ipv4(): self;
    public function ipv6(): self;
    public function json(): self;
    public function lt(string $field): self;
    public function lte(string $field): self;
    public function max(int $value): self;
    public function mimes(string ...$extension): self;
    public function mimetypes(string ...$mimeType): self;
    public function min(int $value): self;
    public function multipleOf($value): self;
    public function notRegex(string $pattern): self;
    public function nullable(): self;
    public function numeric(): self;
    public function password(): self;
    public function present(): self;
    public function prohibited(): self;
    public function prohibitedIf(string $anotherField, string ...$value): self;
    public function prohibitedUnless(string $anotherField, string ...$value): self;
    public function regex(string $pattern): self;
    public function required(): self;
    public function requiredIfAll(RequiredIf ...$rules): self;
    public function requiredIfAny(RequiredIf ...$rules): self;
    public function requiredIfAnyValue(string $anotherField, string ...$value): self;
    public function requiredUnless(string $anotherField, string ...$value): self;
    public function requiredWith(string ...$field): self;
    public function requiredWithAll(string ...$field): self;
    public function requiredWithout(string ...$field): self;
    public function requiredWithoutAll(string ...$field): self;
    public function same(string $field): self;
    public function size(int $value): self;
    public function sometimes(): self;
    public function startsWith(string ...$value): self;
    public function string(): self;
    public function timezone(): self;
    public function url(): self;
    public function uuid(): self;
}
