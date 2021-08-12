<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\RequiredIf;

class RuleSet implements Contracts\RuleSet, Arrayable
{
    private Collection $rules;

    public function __construct(array $rules = [])
    {
        $this->rules = collect($rules);
    }

    public function toArray(): array
    {
        return $this->rules->toArray();
    }

    public function concat(...$rule): self
    {
        $this->rules->push(...$rule);

        return $this;
    }

    public function requiredIfAny(RequiredIf ...$rules): self
    {
        return $this->concat(Rule::requiredIfAny(...$rules));
    }

    public function requiredIfAll(RequiredIf ...$rules): self
    {
        return $this->concat(Rule::requiredIfAll(...$rules));
    }

    public function accepted(): self
    {
        return $this->concat(Rule::accepted());
    }

    public function activeUrl(): self
    {
        return $this->concat(Rule::activeUrl());
    }

    public function after($date): self
    {
        return $this->concat(Rule::after($date));
    }

    public function afterOrEqual($date): self
    {
        return $this->concat(Rule::afterOrEqual($date));
    }

    public function alpha(): self
    {
        return $this->concat(Rule::alpha());
    }

    public function alphaDash(): self
    {
        return $this->concat(Rule::alphaDash());
    }

    public function alphaNum(): self
    {
        return $this->concat(Rule::alphaNum());
    }

    public function array(string ...$requiredKey): self
    {
        return $this->concat(Rule::array(...$requiredKey));
    }

    public function bail(): self
    {
        return $this->concat(Rule::bail());
    }

    public function before($date): self
    {
        return $this->concat(Rule::before($date));
    }

    public function beforeOrEqual($date): self
    {
        return $this->concat(Rule::beforeOrEqual($date));
    }

    public function between(int $min, int $max): self
    {
        return $this->concat(Rule::between($min, $max));
    }

    public function boolean(): self
    {
        return $this->concat(Rule::boolean());
    }

    public function confirmed(): self
    {
        return $this->concat(Rule::confirmed());
    }

    public function currentPassword(?string $authenticationGuard = null): self
    {
        return $this->concat(Rule::currentPassword($authenticationGuard));
    }

    public function date(): self
    {
        return $this->concat(Rule::date());
    }

    public function dateEquals($date): self
    {
        return $this->concat(Rule::dateEquals($date));
    }

    public function dateFormat(string $dateFormat): self
    {
        return $this->concat(Rule::dateFormat($dateFormat));
    }

    public function different(string $field): self
    {
        return $this->concat(Rule::different($field));
    }

    public function digits(int $count): self
    {
        return $this->concat(Rule::digits($count));
    }

    public function digitsBetween(int $min, int $max): self
    {
        return $this->concat(Rule::digitsBetween($min, $max));
    }

    public function distinct(bool $strict = false, $ignoreCase = false): self
    {
        return $this->concat(Rule::distinct($strict, $ignoreCase));
    }

    public function email(string ...$validator): self
    {
        return $this->concat(Rule::email(...$validator));
    }

    public function endsWith(string ...$value): self
    {
        return $this->concat(Rule::endsWith(...$value));
    }

    public function excludeIf(string $anotherField, ?string $value): self
    {
        return $this->concat(Rule::excludeIf($anotherField, $value));
    }

    public function excludeUnless(string $anotherField, ?string $value): self
    {
        return $this->concat(Rule::excludeUnless($anotherField, $value));
    }

    public function file(): self
    {
        return $this->concat(Rule::file());
    }

    public function filled(): self
    {
        return $this->concat(Rule::filled());
    }

    public function gt(string $field): self
    {
        return $this->concat(Rule::gt($field));
    }

    public function gte(string $field): self
    {
        return $this->concat(Rule::gte($field));
    }

    public function image(): self
    {
        return $this->concat(Rule::image());
    }

    public function inArray(string $anotherField): self
    {
        return $this->concat(Rule::inArray($anotherField));
    }

    public function integer(): self
    {
        return $this->concat(Rule::integer());
    }

    public function ip(): self
    {
        return $this->concat(Rule::ip());
    }

    public function ipv4(): self
    {
        return $this->concat(Rule::ipv4());
    }

    public function ipv6(): self
    {
        return $this->concat(Rule::ipv6());
    }

    public function json(): self
    {
        return $this->concat(Rule::json());
    }

    public function lt(string $field): self
    {
        return $this->concat(Rule::lt($field));
    }

    public function lte(string $field): self
    {
        return $this->concat(Rule::lte($field));
    }

    public function max(string $value): self
    {
        return $this->concat(Rule::max($value));
    }

    public function mimes(string ...$extension): self
    {
        return $this->concat(Rule::mimes(...$extension));
    }

    public function mimetypes(string ...$mimeType): self
    {
        return $this->concat(Rule::mimetypes(...$mimeType));
    }

    public function min(string $value): self
    {
        return $this->concat(Rule::min($value));
    }

    public function multipleOf(string $value): self
    {
        return $this->concat(Rule::multipleOf($value));
    }

    public function notRegex(string $pattern): self
    {
        return $this->concat(Rule::notRegex($pattern));
    }

    public function nullable(): self
    {
        return $this->concat(Rule::nullable());
    }

    public function numeric(): self
    {
        return $this->concat(Rule::numeric());
    }

    public function password(): self
    {
        return $this->concat(Rule::password());
    }

    public function present(): self
    {
        return $this->concat(Rule::present());
    }

    public function prohibited(): self
    {
        return $this->concat(Rule::prohibited());
    }

    public function prohibitedIf(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::prohibitedIf($anotherField, ...$value));
    }

    public function prohibitedUnless(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::prohibitedUnless($anotherField, ...$value));
    }

    public function regex(string $pattern): self
    {
        return $this->concat(Rule::regex($pattern));
    }

    public function required(): self
    {
        return $this->concat(Rule::required());
    }

    public function requiredUnless(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::requiredUnless($anotherField, ...$value));
    }

    public function requiredWith(string ...$field): self
    {
        return $this->concat(Rule::requiredWith(...$field));
    }

    public function requiredWithAll(string ...$field): self
    {
        return $this->concat(Rule::requiredWithAll(...$field));
    }

    public function requiredWithout(string ...$field): self
    {
        return $this->concat(Rule::requiredWithout(...$field));
    }

    public function requiredWithoutAll(string ...$field): self
    {
        return $this->concat(Rule::requiredWithoutAll(...$field));
    }

    public function same(string $field): self
    {
        return $this->concat(Rule::same($field));
    }

    public function size(string $value): self
    {
        return $this->concat(Rule::size($value));
    }

    public function sometimes(): self
    {
        return $this->concat(Rule::sometimes());
    }

    public function startsWith(string ...$value): self
    {
        return $this->concat(Rule::startsWith(...$value));
    }

    public function string(): self
    {
        return $this->concat(Rule::string());
    }

    public function timezone(): self
    {
        return $this->concat(Rule::timezone());
    }

    public function url(): self
    {
        return $this->concat(Rule::url());
    }

    public function uuid(): self
    {
        return $this->concat(Rule::uuid());
    }
}
