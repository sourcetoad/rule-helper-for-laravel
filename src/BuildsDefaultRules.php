<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use DateTimeInterface;
use InvalidArgumentException;

trait BuildsDefaultRules
{
    /**
     * The field under validation must be "yes", "on", 1, or true. This is useful for validating "Terms of Service"
     * acceptance or similar fields.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-accepted
     */
    public static function accepted(): string
    {
        return 'accepted';
    }

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-active-url
     */
    public static function activeUrl(): string
    {
        return 'active_url';
    }

    /**
     * The field under validation must be a value after a given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public static function after($date): string
    {
        return 'after:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be a value after or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-after-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public static function afterOrEqual($date): string
    {
        return 'after_or_equal:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha
     */
    public static function alpha(): string
    {
        return 'alpha';
    }

    /**
     * The field under validation may have alpha-numeric characters, as well as dashes and underscores.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-dash
     */
    public static function alphaDash(): string
    {
        return 'alpha_dash';
    }

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-alpha-num
     */
    public static function alphaNum(): string
    {
        return 'alpha_num';
    }

    /**
     * The field under validation must be a PHP *array*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-array
     */
    public static function array(string ...$requiredKey): string
    {
        if (count($requiredKey)) {
            return 'array:'.implode(',', $requiredKey);
        }

        return 'array';
    }

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-bail
     */
    public static function bail(): string
    {
        return 'bail';
    }

    /**
     * The field under validation must be a value preceding the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public static function before($date): string
    {
        return 'before:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be a value preceding or equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-before-or-equal
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public static function beforeOrEqual($date): string
    {
        return 'before_or_equal:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must have a size between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-between
     */
    public static function between(int $min, int $max): string
    {
        return sprintf('between:%d,%d', $min, $max);
    }

    /**
     * The field under validation must be able to be cast as boolean.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-boolean
     */
    public static function boolean(): string
    {
        return 'boolean';
    }

    /**
     * The field under validation must have a matching field of *{field}_confirmation*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-confirmed
     */
    public static function confirmed(): string
    {
        return 'confirmed';
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-current-password
     */
    public static function currentPassword(?string $authenticationGuard = null): string
    {
        if ($authenticationGuard !== null) {
            return 'current_password:'.$authenticationGuard;
        }

        return 'current_password';
    }

    /**
     * The field under validation must be a valid, non-relative date according to the 'strtotime' PHP function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date
     */
    public static function date(): string
    {
        return 'date';
    }

    /**
     * The field under validation must be equal to the given date.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-equals
     * @param string|DateTimeInterface $date A date parseable by 'strtotime'
     */
    public static function dateEquals($date): string
    {
        return 'date_equals:'.static::convertDateForRule($date, 'Y-m-d');
    }

    /**
     * The field under validation must match the given *format*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     * @param string $dateFormat A format supported by the *DateTime* class
     */
    public static function dateFormat(string $dateFormat): string
    {
        return 'date_format:'.$dateFormat;
    }

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-different
     */
    public static function different(string $field): string
    {
        return 'different:'.$field;
    }

    /**
     * The field under validation must be numeric and must have an exact length of *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits
     */
    public static function digits(int $count): string
    {
        return 'digits:'.$count;
    }

    /**
     * The field under validation must be numeric and must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-digits-between
     */
    public static function digitsBetween(int $min, int $max): string
    {
        return sprintf('digits_between:%d,%d', $min, $max);
    }

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-distinct
     */
    public static function distinct(bool $strict = false, $ignoreCase = false): string
    {
        if ($ignoreCase) {
            return 'distinct:ignore_case';
        }

        if ($strict) {
            return 'distinct:strict';
        }

        return 'distinct';
    }

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-email
     */
    public static function email(string ...$validator): string
    {
        if (count($validator)) {
            return 'email:'.implode(',', $validator);
        }

        return 'email';
    }

    /**
     * The field under validation must end with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ends-with
     */
    public static function endsWith(string ...$value): string
    {
        return 'ends_with:'.implode(',', $value);
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*.
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-if
     */
    public static function excludeIf(string $anotherField, ?string $value): string
    {
        return sprintf('exclude_if:%s,%s', $anotherField, $value ?? 'null');
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-exclude-unless
     */
    public static function excludeUnless(string $anotherField, ?string $value): string
    {
        return sprintf('exclude_unless:%s,%s', $anotherField, $value ?? 'null');
    }

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-file
     */
    public static function file(): string
    {
        return 'file';
    }

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-filled
     */
    public static function filled(): string
    {
        return 'filled';
    }

    /**
     * The field under validation must be greater than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gt
     */
    public static function gt(string $field): string
    {
        return 'gt:'.$field;
    }

    /**
     * The field under validation must be greater than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-gte
     */
    public static function gte(string $field): string
    {
        return 'gte:'.$field;
    }

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-image
     */
    public static function image(): string
    {
        return 'image';
    }

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-in-array
     */
    public static function inArray(string $anotherField): string
    {
        return 'in_array:'.$anotherField;
    }

    /**
     * The field under validation must be an integer.
     *
     * NOTE: This validation rule does not verify that the input is of the "integer" variable type, only that the input
     * is of a type accepted by PHP's FILTER_VALIDATE_INT rule.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-integer
     */
    public static function integer(): string
    {
        return 'integer';
    }

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ip
     */
    public static function ip(): string
    {
        return 'ip';
    }

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv4
     */
    public static function ipv4(): string
    {
        return 'ipv4';
    }

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-ipv6
     */
    public static function ipv6(): string
    {
        return 'ipv6';
    }

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-json
     */
    public static function json(): string
    {
        return 'json';
    }

    /**
     * The field under validation must be less than the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lt
     */
    public static function lt(string $field): string
    {
        return 'lt:'.$field;
    }

    /**
     * The field under validation must be less than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-lte
     */
    public static function lte(string $field): string
    {
        return 'lte:'.$field;
    }

    /**
     * The field under validation must be less than or equal to a maximum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-max
     */
    public static function max(string $value): string
    {
        return 'max:'.$value;
    }

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public static function mimes(string ...$extension): string
    {
        return 'mimes:'.implode(',', $extension);
    }

    /**
     * The file under validation must match one of the given MIME types.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-mimetypes
     */
    public static function mimetypes(string ...$mimeType): string
    {
        return 'mimetypes:'.implode(',', $mimeType);
    }

    /**
     * The field under validation must have a minimum *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-min
     */
    public static function min(string $value): string
    {
        return 'min:'.$value;
    }

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#multiple-of
     */
    public static function multipleOf(string $value): string
    {
        return 'multiple_of:'.$value;
    }

    /**
     * The field under validation must not match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-not-regex
     */
    public static function notRegex(string $pattern): string
    {
        return 'not_regex:'.$pattern;
    }

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-nullable
     */
    public static function nullable(): string
    {
        return 'nullable';
    }

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-numeric
     * @link https://www.php.net/manual/en/function.is-numeric.php
     */
    public static function numeric(): string
    {
        return 'numeric';
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-password
     * @deprecated To be removed in 9.0, use currentPassword instead.
     */
    public static function password(): string
    {
        return 'password';
    }

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-present
     */
    public static function present(): string
    {
        return 'present';
    }

    /**
     * The field under validation must be empty or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited
     */
    public static function prohibited(): string
    {
        return 'prohibited';
    }

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-if
     */
    public static function prohibitedIf(string $anotherField, string ...$value): string
    {
        return sprintf('prohibited_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-prohibited-unless
     */
    public static function prohibitedUnless(string $anotherField, string ...$value): string
    {
        return sprintf('prohibited_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must match the given regular expression.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-regex
     */
    public static function regex(string $pattern): string
    {
        return 'regex:'.$pattern;
    }

    /**
     * The field under validation must be present in the input data and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required
     */
    public static function required(): string
    {
        return 'required';
    }

    /**
     * The field under validation must be present and not empty if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-if
     */
    public static function requiredIfAnyValue(string $anotherField, string ...$value): string
    {
        return sprintf('required_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-unless
     */
    public static function requiredUnless(string $anotherField, string ...$value): string
    {
        return sprintf('required_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with
     */
    public static function requiredWith(string ...$field): string
    {
        return 'required_with:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-with-all
     */
    public static function requiredWithAll(string ...$field): string
    {
        return 'required_with_all:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without
     */
    public static function requiredWithout(string ...$field): string
    {
        return 'required_without:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-required-without-all
     */
    public static function requiredWithoutAll(string ...$field): string
    {
        return 'required_without_all:'.implode(',', $field);
    }

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-same
     */
    public static function same(string $field): string
    {
        return 'same:'.$field;
    }

    /**
     * The field under validation must have a size matching the given *value*.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-size
     */
    public static function size(string $value): string
    {
        return 'size:'.$value;
    }

    /**
     * The field under validation will be validated *only* if that field is present in the data.
     *
     * Note: Must be used with other rules to have any effect.
     *
     * @link https://laravel.com/docs/8.x/validation#validating-when-present
     */
    public static function sometimes(): string
    {
        return 'sometimes';
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-starts-with
     */
    public static function startsWith(string ...$value): string
    {
        return 'starts_with:'.implode(',', $value);
    }

    /**
     * The field under validation must be a string.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-string
     */
    public static function string(): string
    {
        return 'string';
    }

    /**
     * The field under validation must be a valid timezone identifier according to the *timezone_identifiers_list* PHP
     * function.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-timezone
     */
    public static function timezone(): string
    {
        return 'timezone';
    }

    /**
     * The field under validation must be a valid URL.
     *
     * @link https://laravel.com/docs/8.x/validation#rule-url
     */
    public static function url(): string
    {
        return 'url';
    }

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/8.x/validation#rule-uuid
     */
    public static function uuid(): string
    {
        return 'uuid';
    }

    /**
     * @param string|DateTimeInterface $date
     * @return string
     */
    private static function convertDateForRule($date, string $format = DateTimeInterface::RFC3339): string
    {
        if (is_string($date)) {
            return $date;
        }

        if ($date instanceof DateTimeInterface) {
            return $date->format($format);
        }

        throw new InvalidArgumentException('Invalid date type supplied');
    }
}
