<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Contracts;

use DateTimeInterface;
use Illuminate\Validation\Rules\RequiredIf;

interface RuleSet
{
    public function concat(...$rule): self;

    public function requiredIfAny(RequiredIf ...$rules): self;

    public function requiredIfAll(RequiredIf ...$rules): self;

    /**
     * The field under validation must be "yes", "on", 1, or true. This is useful for validating "Terms of Service"
     * acceptance or similar fields.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-accepted
     */
    public function accepted(): self;

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-active-url
     */
    public function activeUrl(): self;

    /**
     * The field under validation must be a value after a given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function after($date): self;

    /**
     * The field under validation must be a value after or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function afterOrEqual($date): self;

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha
     */
    public function alpha(): self;

    /**
     * The field under validation may have alpha-numeric characters, as well as dashes and underscores.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-dash
     */
    public function alphaDash(): self;

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-num
     */
    public function alphaNum(): self;

    /**
     * The field under validation must be a PHP *array*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-array
     */
    public function array(string ...$requiredKey): self;

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-bail
     */
    public function bail(): self;

    /**
     * The field under validation must be a value preceding the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function before($date): self;

    /**
     * The field under validation must be a value preceding or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function beforeOrEqual($date): self;

    /**
     * The field under validation must have a size between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-between
     */
    public function between(int $min, int $max): self;

    /**
     * The field under validation must be able to be cast as boolean.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-boolean
     */
    public function boolean(): self;

    /**
     * The field under validation must have a matching field of *{field}_confirmation*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-confirmed
     */
    public function confirmed(): self;

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-current-password
     */
    public function currentPassword(?string $authenticationGuard = null): self;

    /**
     * The field under validation must be a valid, non-relative date according to the 'strtotime' PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date
     */
    public function date(): self;

    /**
     * The field under validation must be equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-equals
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public function dateEquals($date): self;

    /**
     * The field under validation must match the given *format*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     * @param string $dateFormat A format supported by the *DateTime* class
     */
    public function dateFormat(string $dateFormat): self;

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-different
     */
    public function different(string $field): self;

    /**
     * The field under validation must be numeric and must have an exact length of *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits
     */
    public function digits(int $count): self;

    /**
     * The field under validation must be numeric and must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits-between
     */
    public function digitsBetween(int $min, int $max): self;

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-distinct
     */
    public function distinct(bool $strict = false, $ignoreCase = false): self;

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-email
     */
    public function email(string ...$validator): self;

    /**
     * The field under validation must end with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ends-with
     */
    public function endsWith(string ...$value): self;

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*.
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-if
     */
    public function excludeIf(string $anotherField, ?string $value): self;

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-unless
     */
    public function excludeUnless(string $anotherField, ?string $value): self;

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-file
     */
    public function file(): self;

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-filled
     */
    public function filled(): self;

    /**
     * The field under validation must be greater than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gt
     */
    public function gt(string $field): self;

    /**
     * The field under validation must be greater than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gte
     */
    public function gte(string $field): self;

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-image
     */
    public function image(): self;

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-in-array
     */
    public function inArray(string $anotherField): self;

    /**
     * The field under validation must be an integer.
     *
     * NOTE: This validation rule does not verify that the input is of the "integer" variable type, only that the input
     * is of a type accepted by PHP's FILTER_VALIDATE_INT rule.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-integer
     */
    public function integer(): self;

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ip
     */
    public function ip(): self;

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv4
     */
    public function ipv4(): self;

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv6
     */
    public function ipv6(): self;

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-json
     */
    public function json(): self;

    /**
     * The field under validation must be less than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lt
     */
    public function lt(string $field): self;

    /**
     * The field under validation must be less than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lte
     */
    public function lte(string $field): self;

    /**
     * The field under validation must be less than or equal to a maximum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-max
     */
    public function max(string $value): self;

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public function mimes(string ...$extension): self;

    /**
     * The file under validation must match one of the given MIME types.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimetypes
     */
    public function mimetypes(string ...$mimeType): self;

    /**
     * The field under validation must have a minimum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-min
     */
    public function min(string $value): self;

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#multiple-of
     */
    public function multipleOf(string $value): self;

    /**
     * The field under validation must not match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-not-regex
     */
    public function notRegex(string $pattern): self;

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-nullable
     */
    public function nullable(): self;

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-numeric
     * @link https://www.php.net/manual/en/function.is-numeric.php
     */
    public function numeric(): self;

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-password
     * @deprecated To be removed in 9.0, use currentPassword instead.
     */
    public function password(): self;

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-present
     */
    public function present(): self;

    /**
     * The field under validation must be empty or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited
     */
    public function prohibited(): self;

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-if
     */
    public function prohibitedIf(string $anotherField, string ...$value): self;

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-unless
     */
    public function prohibitedUnless(string $anotherField, string ...$value): self;

    /**
     * The field under validation must match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-regex
     */
    public function regex(string $pattern): self;

    /**
     * The field under validation must be present in the input data and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required
     */
    public function required(): self;

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-unless
     */
    public function requiredUnless(string $anotherField, string ...$value): self;

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with
     */
    public function requiredWith(string ...$field): self;

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with-all
     */
    public function requiredWithAll(string ...$field): self;

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without
     */
    public function requiredWithout(string ...$field): self;

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without-all
     */
    public function requiredWithoutAll(string ...$field): self;

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-same
     */
    public function same(string $field): self;

    /**
     * The field under validation must have a size matching the given *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-size
     */
    public function size(string $value): self;

    /**
     * The field under validation will be validated *only* if that field is present in the data.
     *
     * Note: Must be used with other rules to have any effect.
     *
     * @link https://laravel.com/docs/8.x/validation#validating-when-present
     */
    public function sometimes(): self;

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-starts-with
     */
    public function startsWith(string ...$value): self;

    /**
     * The field under validation must be a string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-string
     */
    public function string(): self;

    /**
     * The field under validation must be a valid timezone identifier according to the *timezone_identifiers_list* PHP
     * function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-timezone
     */
    public function timezone(): self;

    /**
     * The field under validation must be a valid URL.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-url
     */
    public function url(): self;

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-uuid
     */
    public function uuid(): self;
}
