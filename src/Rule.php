<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use BackedEnum;
use Brick\Math\BigNumber;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Validation\ConditionalRules;
use Illuminate\Validation\Rule as LaravelRule;
use Illuminate\Validation\Rules\ArrayRule;
use Illuminate\Validation\Rules\Can;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\NotIn;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\ProhibitedIf;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Unique;
use Stringable;
use UnitEnum;

class Rule
{
    /**
     * The field under validation must be "yes", "on", 1, or true. This is useful for validating "Terms of Service"
     * acceptance or similar fields.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-accepted
     */
    public static function accepted(): string
    {
        return 'accepted';
    }

    /**
     * The field under validation must be "yes", "on", 1, or true if *anotherField* under validation is equal to a
     * specified *value*. This is useful for validating "Terms of Service" acceptance or similar fields.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-accepted-if
     */
    public static function acceptedIf(string $anotherField, string ...$value): string
    {
        return sprintf('accepted_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-active-url
     */
    public static function activeUrl(): string
    {
        return 'active_url';
    }

    /**
     * The field under validation must be a value after a given date.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-after
     */
    public static function after(string|DateTimeInterface $date): string
    {
        return 'after:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be a value after or equal to the given date.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-after-or-equal
     */
    public static function afterOrEqual(string|DateTimeInterface $date): string
    {
        return 'after_or_equal:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-alpha
     */
    public static function alpha(): string
    {
        return 'alpha';
    }

    /**
     * The field under validation may have alpha-numeric characters, as well as dashes and underscores.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-alpha-dash
     */
    public static function alphaDash(): string
    {
        return 'alpha_dash';
    }

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-alpha-num
     */
    public static function alphaNum(): string
    {
        return 'alpha_num';
    }

    /**
     * The field under validation must be a PHP *array*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-array
     */
    public static function array(BackedEnum|UnitEnum|string ...$requiredKey): ArrayRule
    {
        if (count($requiredKey)) {
            return LaravelRule::array($requiredKey);
        }

        return LaravelRule::array();
    }

    /**
     * The field under validation must be entirely 7-bit ASCII characters.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-ascii
     */
    public static function ascii(): string
    {
        return 'ascii';
    }

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-bail
     */
    public static function bail(): string
    {
        return 'bail';
    }

    /**
     * The field under validation must be a value preceding the given date.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-before
     */
    public static function before(string|DateTimeInterface $date): string
    {
        return 'before:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must be a value preceding or equal to the given date.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-before-or-equal
     */
    public static function beforeOrEqual(string|DateTimeInterface $date): string
    {
        return 'before_or_equal:'.static::convertDateForRule($date);
    }

    /**
     * The field under validation must have a size between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-between
     */
    public static function between(float|int|string|BigNumber $min, float|int|string|BigNumber $max): string
    {
        return sprintf('between:%s,%s', $min, $max);
    }

    /**
     * The field under validation must be able to be cast as a boolean. Accepted input are true, false, 1, 0, "1", and
     * "0".
     *
     * @link https://laravel.com/docs/11.x/validation#rule-boolean
     */
    public static function boolean(): string
    {
        return 'boolean';
    }

    /**
     * The field under validation must pass a Gate check for the specified ability.
     *
     * @link https://laravel.com/docs/11.x/authorization#gates
     */
    public static function can(string $ability, mixed ...$arguments): Can
    {
        return LaravelRule::can($ability, ...$arguments);
    }

    /**
     * The field under validation must have a matching field of *{field}_confirmation*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-confirmed
     */
    public static function confirmed(?string $confirmationFieldName = null): string
    {
        if ($confirmationFieldName !== null) {
            return 'confirmed:'.$confirmationFieldName;
        }

        return 'confirmed';
    }

    /**
     * The field under validation must be an array that contains all of the given parameter values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-contains
     */
    public static function contains(mixed ...$value): string
    {
        return 'contains:'.implode(',', $value);
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-current-password
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
     * @link https://laravel.com/docs/11.x/validation#rule-date
     */
    public static function date(): string
    {
        return 'date';
    }

    /**
     * The field under validation must be equal to the given date.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-date-equals
     */
    public static function dateEquals(string|DateTimeInterface $date): string
    {
        return 'date_equals:'.static::convertDateForRule($date, 'Y-m-d');
    }

    /**
     * The field under validation must match the given *format*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     * @param string $dateFormat A format supported by the *DateTime* class
     */
    public static function dateFormat(string $dateFormat): string
    {
        return 'date_format:'.$dateFormat;
    }

    /**
     * The field under validation must be numeric and must contain the specified number of decimal places.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-decimal
     */
    public static function decimal(int $precision, ?int $maxPrecision = null): string
    {
        return 'decimal:'.$precision.($maxPrecision !== null ? ','.$maxPrecision : '');
    }

    /**
     * The field under validation must be *"no"*, *"off"*, *0*, or *false*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-declined
     */
    public static function declined(): string
    {
        return 'declined';
    }

    /**
     * The field under validation must be *"no"*, *"off"*, *0*, or *false* if *anotherField* under validation is equal
     * to a specified value.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-declined-if
     */
    public static function declinedIf(string $anotherField, string ...$value): string
    {
        return sprintf('declined_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-different
     */
    public static function different(string $field): string
    {
        return 'different:'.$field;
    }

    /**
     * The field under validation must be numeric and must have an exact length of *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-digits
     */
    public static function digits(int $count): string
    {
        return 'digits:'.$count;
    }

    /**
     * The field under validation must be numeric and must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-digits-between
     */
    public static function digitsBetween(int $min, int $max): string
    {
        return sprintf('digits_between:%d,%d', $min, $max);
    }

    /**
     * The file under validation must be an image meeting the dimension constraints as specified by the rule's
     * parameters.
     *
     * Available constraints are: *min_width*, *max_width*, *min_height*, *max_height*, *width*, *height*, *ratio*.
     *
     * A ratio constraint should be represented as width divided by height. This can be specified either by a fraction
     * like *3/2* or a float like *1.5*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-dimensions
     * @param array<string, int|float|string> $constraints
     */
    public static function dimensions(array $constraints = []): Dimensions
    {
        return LaravelRule::dimensions($constraints);
    }

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-distinct
     */
    public static function distinct(bool $strict = false, bool $ignoreCase = false): string
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
     * The field under validation must not end with one of the given values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-doesnt-end-with
     */
    public static function doesntEndWith(string ...$value): string
    {
        return 'doesnt_end_with:'.implode(',', $value);
    }

    /**
     * The field under validation must not start with one of the given values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-doesnt-start-with
     */
    public static function doesntStartWith(string ...$value): string
    {
        return 'doesnt_start_with:'.implode(',', $value);
    }

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-email
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
     * @link https://laravel.com/docs/11.x/validation#rule-ends-with
     */
    public static function endsWith(string ...$value): string
    {
        return 'ends_with:'.implode(',', $value);
    }

    /**
     * The field under validation contains a valid enum value of the specified type.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-enum
     * @param class-string $type
     */
    public static function enum(string $type): Enum
    {
        return LaravelRule::enum($type);
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude
     */
    public static function exclude(): string
    {
        return 'exclude';
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if a true boolean is passed in or the passed in closure returns true.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude-if
     * @param bool|callable(): bool $callback
     */
    public static function excludeIf(mixed $callback): ExcludeIf
    {
        return LaravelRule::excludeIf($callback);
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude-if
     */
    public static function excludeIfValue(string $anotherField, ?string $value): string
    {
        return sprintf('exclude_if:%s,%s', $anotherField, $value ?? 'null');
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude-unless
     */
    public static function excludeUnless(string $anotherField, ?string $value): string
    {
        return sprintf('exclude_unless:%s,%s', $anotherField, $value ?? 'null');
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude-with
     */
    public static function excludeWith(string $anotherField): string
    {
        return 'exclude_with:'.$anotherField;
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is not present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exclude-without
     */
    public static function excludeWithout(string $anotherField): string
    {
        return 'exclude_without:'.$anotherField;
    }

    /**
     * The field under validation must exist in a given database table. If the *column* option is not specified, the
     * field name will be used. Instead of specifying the table name directly, you may specify the Eloquent model class
     * name.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-exists
     */
    public static function exists(string $table, string $column = 'NULL'): Exists
    {
        return LaravelRule::exists($table, $column);
    }

    /**
     * The file under validation must have a user-assigned extension corresponding to one of the listed extensions.
     *
     * Warning: You should never rely on validating a file by its user-assigned extension alone. This rule should
     *          typically always be used in combination with the {@see Rule::mimes} or {@see Rule::mimetypes} rules.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-extensions
     */
    public static function extensions(string ...$extension): string
    {
        return 'extensions:'.implode(',', $extension);
    }

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-file
     */
    public static function file(): string
    {
        return 'file';
    }

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-filled
     */
    public static function filled(): string
    {
        return 'filled';
    }

    /**
     * The field under validation must be greater than the given *field*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-gt
     */
    public static function gt(BigNumber|int|float|string $field): string
    {
        return sprintf('gt:%s', $field);
    }

    /**
     * The field under validation must be greater than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-gte
     */
    public static function gte(BigNumber|int|float|string $field): string
    {
        return sprintf('gte:%s', $field);
    }

    /**
     * The field under validation must contain a valid color value in hexadecimal format.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-hex-color
     * @link https://developer.mozilla.org/en-US/docs/Web/CSS/hex-color
     */
    public static function hexColor(): string
    {
        return 'hex_color';
    }

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/11.x/validation#rule-image
     */
    public static function image(): string
    {
        return 'image';
    }

    /**
     * The field under validation must be included in the given list of values.
     *
     * When the *in* rule is combined with the *array* rule, each value in the input array must be present within the
     * list of values provided to the *in* rule.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-in
     * @param Arrayable<array-key, BackedEnum|UnitEnum|string>|array<BackedEnum|UnitEnum|string>|BackedEnum|UnitEnum|string $values
     */
    public static function in(Arrayable|BackedEnum|UnitEnum|array|string $values): In
    {
        return LaravelRule::in($values);
    }

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-in-array
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
     * @link https://laravel.com/docs/11.x/validation#rule-integer
     */
    public static function integer(): string
    {
        return 'integer';
    }

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-ip
     */
    public static function ip(): string
    {
        return 'ip';
    }

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/11.x/validation#ipv4
     */
    public static function ipv4(): string
    {
        return 'ipv4';
    }

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/11.x/validation#ipv6
     */
    public static function ipv6(): string
    {
        return 'ipv6';
    }

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-json
     */
    public static function json(): string
    {
        return 'json';
    }

    /**
     * The field under validation must be an array that is a list. An array is considered a list if its keys consist of
     * consecutive numbers from 0 to count($array) - 1.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-list
     */
    public static function list(): string
    {
        return 'list';
    }

    /**
     * The field under validation must be lowercase.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-lowercase
     */
    public static function lowercase(): string
    {
        return 'lowercase';
    }

    /**
     * The field under validation must be less than the given *field*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-lt
     */
    public static function lt(BigNumber|int|float|string $field): string
    {
        return sprintf('lt:%s', $field);
    }

    /**
     * The field under validation must be less than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-lte
     */
    public static function lte(BigNumber|int|float|string $field): string
    {
        return sprintf('lte:%s', $field);
    }

    /**
     * The field under validation must be a MAC address.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-mac
     */
    public static function macAddress(): string
    {
        return 'mac_address';
    }

    /**
     * The field under validation must be less than or equal to a maximum *value*. Strings, numerics, arrays, and files
     * are evaluated in the same fashion as the *size* rule.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-max
     */
    public static function max(BigNumber|int|float|string $value): string
    {
        return sprintf('max:%s', $value);
    }

    /**
     * The integer under validation must have a maximum length of *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-max-digits
     */
    public static function maxDigits(int $value): string
    {
        return 'max_digits:'.$value;
    }

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public static function mimes(string ...$extension): string
    {
        return 'mimes:'.implode(',', $extension);
    }

    /**
     * The file under validation must match one of the given MIME types.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-mimetypes
     */
    public static function mimetypes(string ...$mimeType): string
    {
        return 'mimetypes:'.implode(',', $mimeType);
    }

    /**
     * The field under validation must have a minimum *value*. Strings, numerics, arrays, and files are evaluated in the
     * same fashion as the *size* rule.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-min
     */
    public static function min(BigNumber|int|float|string $value): string
    {
        return sprintf('min:%s', $value);
    }

    /**
     * The integer under validation must have a minimum length of *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-min-digits
     */
    public static function minDigits(int $value): string
    {
        return 'min_digits:'.$value;
    }

    /**
     * The field under validation must not be present in the input data.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-missing
     */
    public static function missing(): string
    {
        return 'missing';
    }

    /**
     * The field under validation must not be present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-missing-if
     */
    public static function missingIf(string $anotherField, string ...$value): string
    {
        return sprintf('missing_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must not be present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-missing-unless
     */
    public static function missingUnless(string $anotherField, string ...$value): string
    {
        return sprintf('missing_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must not be present *only if* any of the other specified fields are present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-missing-with
     */
    public static function missingWith(string ...$field): string
    {
        return sprintf('missing_with:%s', implode(',', $field));
    }

    /**
     * The field under validation must not be present *only if* all of the other specified fields are present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-missing-with-all
     */
    public static function missingWithAll(string ...$field): string
    {
        return sprintf('missing_with_all:%s', implode(',', $field));
    }

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-multiple-of
     */
    public static function multipleOf(int|float $value): string
    {
        return 'multiple_of:'.$value;
    }

    /**
     * The field under validation must not be included in the given list of values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-not-in
     * @param Arrayable<array-key, BackedEnum|UnitEnum|string>|array<BackedEnum|UnitEnum|string>|BackedEnum|UnitEnum|string $values
     */
    public static function notIn(Arrayable|BackedEnum|UnitEnum|array|string $values): NotIn
    {
        return LaravelRule::notIn($values);
    }

    /**
     * The field under validation must not match the given regular expression.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-not-regex
     */
    public static function notRegex(string $pattern): string
    {
        return 'not_regex:'.$pattern;
    }

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-nullable
     */
    public static function nullable(): string
    {
        return 'nullable';
    }

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-numeric
     * @link https://www.php.net/manual/en/function.is-numeric.php
     */
    public static function numeric(): string
    {
        return 'numeric';
    }

    /**
     * The field under validation must be a string with an adequate level of complexity for a password. Defaults to a
     * minimum of 8 characters if no size is provided and {@see Password::defaults} was not used.
     *
     * @link https://laravel.com/docs/11.x/validation#validating-passwords
     */
    public static function password(?int $size = null): Password
    {
        return $size === null
            ? Password::default()
            : Password::min($size);
    }

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-present
     */
    public static function present(): string
    {
        return 'present';
    }

    /**
     * The field under validation must be present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-present-if
     */
    public static function presentIf(string $anotherField, string ...$value): string
    {
        return sprintf('present_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-present-unless
     */
    public static function presentUnless(string $anotherField, string ...$value): string
    {
        return sprintf('present_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present *only if* any of the other specified fields are present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-present-with
     */
    public static function presentWith(string ...$field): string
    {
        return 'present_with:'.implode(',', $field);
    }

    /**
     * The field under validation must be present *only if* all the other specified fields are present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-present-with-all
     */
    public static function presentWithAll(string ...$field): string
    {
        return 'present_with_all:'.implode(',', $field);
    }

    /**
     * The field under validation must be empty or not present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-prohibited
     */
    public static function prohibited(): string
    {
        return 'prohibited';
    }

    /**
     * The field under validation must be empty or not present in the input data if a true boolean is passed in or the
     * passed in closure returns true.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-prohibited-if
     * @param bool|callable(): bool $callback
     */
    public static function prohibitedIf(mixed $callback): ProhibitedIf
    {
        return LaravelRule::prohibitedIf($callback);
    }

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-prohibited-if
     */
    public static function prohibitedIfValue(string $anotherField, string ...$value): string
    {
        return sprintf('prohibited_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-prohibited-unless
     */
    public static function prohibitedUnless(string $anotherField, string ...$value): string
    {
        return sprintf('prohibited_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * If the field under validation is present, no fields in *anotherField* can be present, even if empty.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-prohibits
     */
    public static function prohibits(string ...$anotherField): string
    {
        return sprintf('prohibits:%s', implode(',', $anotherField));
    }

    /**
     * The field under validation must match the given regular expression.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-regex
     */
    public static function regex(string $pattern): string
    {
        return 'regex:'.$pattern;
    }

    /**
     * The field under validation must be present in the input data and not empty.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required
     */
    public static function required(): string
    {
        return 'required';
    }

    /**
     * The field under validation must be an array and must contain at least the specified keys.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-array-keys
     */
    public static function requiredArrayKeys(string ...$key): string
    {
        return sprintf('required_array_keys:%s', implode(',', $key));
    }

    /**
     * The field under validation must be present in the input data if a true boolean is passed in or the passed in
     * closure returns true.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-if
     * @param bool|callable(): bool $callback
     */
    public static function requiredIf(mixed $callback): RequiredIf
    {
        return LaravelRule::requiredIf($callback);
    }

    /**
     * The field under validation must be present and not empty if the *field* field is equal to yes, on, 1, "1", true,
     * or "true".
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-if-accepted
     */
    public static function requiredIfAccepted(string $field): string
    {
        return 'required_if_accepted:'.$field;
    }

    /**
     * The field must be present if all the criteria are true.
     */
    public static function requiredIfAll(RequiredIf ...$rules): RequiredIf
    {
        return self::requiredIf(function () use ($rules) {
            return !self::getRuleResults($rules)->containsStrict(false);
        });
    }

    /**
     * The field must be present if any of the criteria are true.
     */
    public static function requiredIfAny(RequiredIf ...$rules): RequiredIf
    {
        return self::requiredIf(function () use ($rules) {
            return self::getRuleResults($rules)->containsStrict(true);
        });
    }

    /**
     * The field under validation must be present and not empty if the *field* field is equal to "no", "off", 0, "0",
     * false, or "false".
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-if-declined
     */
    public static function requiredIfDeclined(string $field): string
    {
        return 'required_if_declined:'.$field;
    }

    /**
     * The field under validation must be present and not empty if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-if
     */
    public static function requiredIfValue(string $anotherField, string ...$value): string
    {
        return sprintf('required_if:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-unless
     */
    public static function requiredUnless(string $anotherField, string ...$value): string
    {
        return sprintf('required_unless:%s,%s', $anotherField, implode(',', $value));
    }

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-with
     */
    public static function requiredWith(string ...$field): string
    {
        return 'required_with:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-with-all
     */
    public static function requiredWithAll(string ...$field): string
    {
        return 'required_with_all:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-without
     */
    public static function requiredWithout(string ...$field): string
    {
        return 'required_without:'.implode(',', $field);
    }

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-required-without-all
     */
    public static function requiredWithoutAll(string ...$field): string
    {
        return 'required_without_all:'.implode(',', $field);
    }

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-same
     */
    public static function same(string $field): string
    {
        return 'same:'.$field;
    }

    /**
     * The field under validation must have a size matching the given *value*.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-size
     */
    public static function size(BigNumber|int|float|string $value): string
    {
        return sprintf('size:%s', $value);
    }

    /**
     * The field under validation will be validated *only* if that field is present in the data.
     *
     * Note: Must be used with other rules to have any effect.
     *
     * @link https://laravel.com/docs/11.x/validation#validating-when-present
     */
    public static function sometimes(): string
    {
        return 'sometimes';
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-starts-with
     */
    public static function startsWith(string ...$value): string
    {
        return 'starts_with:'.implode(',', $value);
    }

    /**
     * The field under validation must be a string.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-string
     */
    public static function string(): string
    {
        return 'string';
    }

    /**
     * The field under validation must be a valid timezone identifier according to the *timezone_identifiers_list* PHP
     * function.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-timezone
     */
    public static function timezone(): string
    {
        return 'timezone';
    }

    /**
     * The field under validation must be a valid Universally Unique Lexicographically Sortable Identifier (ULID).
     *
     * @link https://laravel.com/docs/11.x/validation#rule-ulid
     * @link https://github.com/ulid/spec
     */
    public static function ulid(): string
    {
        return 'ulid';
    }

    /**
     * The field under validation must not exist within the given database table. If the *column* option is not
     * specified, the field name will be used. Instead of specifying the table name directly, you may specify the
     * Eloquent model class name.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-unique
     */
    public static function unique(string $table, string $column = 'NULL'): Unique
    {
        return LaravelRule::unique($table, $column);
    }

    /**
     * The field under validation must be uppercase.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-uppercase
     */
    public static function uppercase(): string
    {
        return 'uppercase';
    }

    /**
     * The field under validation must be a valid URL. If no protocol is specified, all protocols are considered valid.
     *
     * @link https://laravel.com/docs/11.x/validation#rule-url
     */
    public static function url(string ...$protocol): string
    {
        if (count($protocol)) {
            return 'url:'.implode(',', $protocol);
        }

        return 'url';
    }

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/11.x/validation#rule-uuid
     */
    public static function uuid(): string
    {
        return 'uuid';
    }

    /**
     * Create a new conditional rule set.
     *
     * @param bool|callable(\Illuminate\Support\Fluent<array-key, mixed>): bool $condition
     * @param array<array-key, RuleContract|InvokableRule|ValidationRule|ConditionalRules|Stringable|string>|string|RuleSet $rules
     * @param array<array-key, RuleContract|InvokableRule|ValidationRule|ConditionalRules|Stringable|string>|string|RuleSet $defaultRules
     */
    public static function when(
        mixed $condition,
        array|string|RuleSet $rules,
        array|string|RuleSet $defaultRules = []
    ): ConditionalRules {
        if ($rules instanceof RuleSet) {
            $rules = $rules->toArray();
        }
        if ($defaultRules instanceof RuleSet) {
            $defaultRules = $defaultRules->toArray();
        }

        return LaravelRule::when($condition, $rules, $defaultRules);
    }

    protected static function convertDateForRule(
        string|DateTimeInterface $date,
        string $format = DateTimeInterface::RFC3339
    ): string {
        if (is_string($date)) {
            return $date;
        } else {
            return $date->format($format);
        }
    }

    /**
     * @param array<array-key, RequiredIf> $rules
     * @return Collection<array-key, bool>
     */
    protected static function getRuleResults(array $rules): Collection
    {
        return collect($rules)
            ->map(
                fn($rule) => is_callable($rule->condition)
                    ? call_user_func($rule->condition)
                    : $rule->condition,
            );
    }
}
