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

    /**
     * Get the rule set as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->rules->toArray();
    }

    /**
     * Create a new rule set.
     *
     * @param array $rules
     * @return static
     */
    public static function create(array $rules = []): self
    {
        return new static($rules);
    }

    /**
     * Append one or more rules to the end of the rule set.
     *
     * @param  \Illuminate\Contracts\Validation\Rule|string $rule
     * @return $this
     */
    public function concat(...$rule): self
    {
        $this->rules->push(...$rule);

        return $this;
    }

    /**
     * The field under validation must be "yes", "on", 1, or true. This is useful for validating "Terms of Service"
     * acceptance or similar fields.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-accepted
     */
    public function accepted(): self
    {
        return $this->concat(Rule::accepted());
    }

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-active-url
     */
    public function activeUrl(): self
    {
        return $this->concat(Rule::activeUrl());
    }

    /**
     * The field under validation must be a value after a given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function after($date): self
    {
        return $this->concat(Rule::after($date));
    }

    /**
     * The field under validation must be a value after or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function afterOrEqual($date): self
    {
        return $this->concat(Rule::afterOrEqual($date));
    }

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha
     */
    public function alpha(): self
    {
        return $this->concat(Rule::alpha());
    }

    /**
     * The field under validation may have alpha-numeric characters, as well as dashes and underscores.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-dash
     */
    public function alphaDash(): self
    {
        return $this->concat(Rule::alphaDash());
    }

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-num
     */
    public function alphaNum(): self
    {
        return $this->concat(Rule::alphaNum());
    }

    /**
     * The field under validation must be a PHP *array*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-array
     */
    public function array(string ...$requiredKey): self
    {
        return $this->concat(Rule::array(...$requiredKey));
    }

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-bail
     */
    public function bail(): self
    {
        return $this->concat(Rule::bail());
    }

    /**
     * The field under validation must be a value preceding the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function before($date): self
    {
        return $this->concat(Rule::before($date));
    }

    /**
     * The field under validation must be a value preceding or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function beforeOrEqual($date): self
    {
        return $this->concat(Rule::beforeOrEqual($date));
    }

    /**
     * The field under validation must have a size between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-between
     */
    public function between(int $min, int $max): self
    {
        return $this->concat(Rule::between($min, $max));
    }

    /**
     * The field under validation must be able to be cast as boolean.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-boolean
     */
    public function boolean(): self
    {
        return $this->concat(Rule::boolean());
    }

    /**
     * The field under validation must have a matching field of *{field}_confirmation*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-confirmed
     */
    public function confirmed(): self
    {
        return $this->concat(Rule::confirmed());
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-current-password
     */
    public function currentPassword(?string $authenticationGuard = null): self
    {
        return $this->concat(Rule::currentPassword($authenticationGuard));
    }

    /**
     * The field under validation must be a valid, non-relative date according to the 'strtotime' PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date
     */
    public function date(): self
    {
        return $this->concat(Rule::date());
    }

    /**
     * The field under validation must be equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-equals
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function dateEquals($date): self
    {
        return $this->concat(Rule::dateEquals($date));
    }

    /**
     * The field under validation must match the given *format*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     * @param string $dateFormat A format supported by the *DateTime* class
     */
    public function dateFormat(string $dateFormat): self
    {
        return $this->concat(Rule::dateFormat($dateFormat));
    }

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-different
     */
    public function different(string $field): self
    {
        return $this->concat(Rule::different($field));
    }

    /**
     * The field under validation must be numeric and must have an exact length of *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits
     */
    public function digits(int $count): self
    {
        return $this->concat(Rule::digits($count));
    }

    /**
     * The field under validation must be numeric and must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits-between
     */
    public function digitsBetween(int $min, int $max): self
    {
        return $this->concat(Rule::digitsBetween($min, $max));
    }

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-distinct
     */
    public function distinct(bool $strict = false, $ignoreCase = false): self
    {
        return $this->concat(Rule::distinct($strict, $ignoreCase));
    }

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-email
     */
    public function email(string ...$validator): self
    {
        return $this->concat(Rule::email(...$validator));
    }

    /**
     * The field under validation must end with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ends-with
     */
    public function endsWith(string ...$value): self
    {
        return $this->concat(Rule::endsWith(...$value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*.
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-if
     */
    public function excludeIf(string $anotherField, ?string $value): self
    {
        return $this->concat(Rule::excludeIf($anotherField, $value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-unless
     */
    public function excludeUnless(string $anotherField, ?string $value): self
    {
        return $this->concat(Rule::excludeUnless($anotherField, $value));
    }

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-file
     */
    public function file(): self
    {
        return $this->concat(Rule::file());
    }

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-filled
     */
    public function filled(): self
    {
        return $this->concat(Rule::filled());
    }

    /**
     * The field under validation must be greater than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gt
     */
    public function gt(string $field): self
    {
        return $this->concat(Rule::gt($field));
    }

    /**
     * The field under validation must be greater than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gte
     */
    public function gte(string $field): self
    {
        return $this->concat(Rule::gte($field));
    }

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-image
     */
    public function image(): self
    {
        return $this->concat(Rule::image());
    }

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-in-array
     */
    public function inArray(string $anotherField): self
    {
        return $this->concat(Rule::inArray($anotherField));
    }

    /**
     * The field under validation must be an integer.
     *
     * NOTE: This validation rule does not verify that the input is of the "integer" variable type, only that the input
     * is of a type accepted by PHP's FILTER_VALIDATE_INT rule.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-integer
     */
    public function integer(): self
    {
        return $this->concat(Rule::integer());
    }

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ip
     */
    public function ip(): self
    {
        return $this->concat(Rule::ip());
    }

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv4
     */
    public function ipv4(): self
    {
        return $this->concat(Rule::ipv4());
    }

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv6
     */
    public function ipv6(): self
    {
        return $this->concat(Rule::ipv6());
    }

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-json
     */
    public function json(): self
    {
        return $this->concat(Rule::json());
    }

    /**
     * The field under validation must be less than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lt
     */
    public function lt(string $field): self
    {
        return $this->concat(Rule::lt($field));
    }

    /**
     * The field under validation must be less than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lte
     */
    public function lte(string $field): self
    {
        return $this->concat(Rule::lte($field));
    }

    /**
     * The field under validation must be less than or equal to a maximum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-max
     */
    public function max(int $value): self
    {
        return $this->concat(Rule::max($value));
    }

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public function mimes(string ...$extension): self
    {
        return $this->concat(Rule::mimes(...$extension));
    }

    /**
     * The file under validation must match one of the given MIME types.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimetypes
     */
    public function mimetypes(string ...$mimeType): self
    {
        return $this->concat(Rule::mimetypes(...$mimeType));
    }

    /**
     * The field under validation must have a minimum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-min
     */
    public function min(int $value): self
    {
        return $this->concat(Rule::min($value));
    }

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @param int|float $value
     * @link https://laravel.com/docs/8.x/validation#multiple-of
     */
    public function multipleOf($value): self
    {
        return $this->concat(Rule::multipleOf($value));
    }

    /**
     * The field under validation must not match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-not-regex
     */
    public function notRegex(string $pattern): self
    {
        return $this->concat(Rule::notRegex($pattern));
    }

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-nullable
     */
    public function nullable(): self
    {
        return $this->concat(Rule::nullable());
    }

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-numeric
     * @link https://www.php.net/manual/en/function.is-numeric.php
     */
    public function numeric(): self
    {
        return $this->concat(Rule::numeric());
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-password
     * @deprecated To be removed in 9.0, use currentPassword instead.
     */
    public function password(): self
    {
        /** @noinspection PhpDeprecationInspection */
        return $this->concat(Rule::password());
    }

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-present
     */
    public function present(): self
    {
        return $this->concat(Rule::present());
    }

    /**
     * The field under validation must be empty or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited
     */
    public function prohibited(): self
    {
        return $this->concat(Rule::prohibited());
    }

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-if
     */
    public function prohibitedIf(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::prohibitedIf($anotherField, ...$value));
    }

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-unless
     */
    public function prohibitedUnless(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::prohibitedUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-regex
     */
    public function regex(string $pattern): self
    {
        return $this->concat(Rule::regex($pattern));
    }

    /**
     * The field under validation must be present in the input data and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required
     */
    public function required(): self
    {
        return $this->concat(Rule::required());
    }

    /**
     * The field must be present if all the criteria are true.
     */
    public function requiredIfAll(RequiredIf ...$rules): self
    {
        return $this->concat(Rule::requiredIfAll(...$rules));
    }

    /**
     * The field must be present if any of the criteria are true.
     */
    public function requiredIfAny(RequiredIf ...$rules): self
    {
        return $this->concat(Rule::requiredIfAny(...$rules));
    }

    /**
     * The field under validation must be present and not empty if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-if
     */
    public function requiredIfAnyValue(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::requiredIfAnyValue($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-unless
     */
    public function requiredUnless(string $anotherField, string ...$value): self
    {
        return $this->concat(Rule::requiredUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with
     */
    public function requiredWith(string ...$field): self
    {
        return $this->concat(Rule::requiredWith(...$field));
    }

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with-all
     */
    public function requiredWithAll(string ...$field): self
    {
        return $this->concat(Rule::requiredWithAll(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without
     */
    public function requiredWithout(string ...$field): self
    {
        return $this->concat(Rule::requiredWithout(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without-all
     */
    public function requiredWithoutAll(string ...$field): self
    {
        return $this->concat(Rule::requiredWithoutAll(...$field));
    }

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-same
     */
    public function same(string $field): self
    {
        return $this->concat(Rule::same($field));
    }

    /**
     * The field under validation must have a size matching the given *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-size
     */
    public function size(int $value): self
    {
        return $this->concat(Rule::size($value));
    }

    /**
     * The field under validation will be validated *only* if that field is present in the data.
     *
     * Note: Must be used with other rules to have any effect.
     *
     * @link https://laravel.com/docs/8.x/validation#validating-when-present
     */
    public function sometimes(): self
    {
        return $this->concat(Rule::sometimes());
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-starts-with
     */
    public function startsWith(string ...$value): self
    {
        return $this->concat(Rule::startsWith(...$value));
    }

    /**
     * The field under validation must be a string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-string
     */
    public function string(): self
    {
        return $this->concat(Rule::string());
    }

    /**
     * The field under validation must be a valid timezone identifier according to the *timezone_identifiers_list* PHP
     * function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-timezone
     */
    public function timezone(): self
    {
        return $this->concat(Rule::timezone());
    }

    /**
     * The field under validation must be a valid URL.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-url
     */
    public function url(): self
    {
        return $this->concat(Rule::url());
    }

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-uuid
     */
    public function uuid(): self
    {
        return $this->concat(Rule::uuid());
    }
}
