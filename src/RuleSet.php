<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use ArrayIterator;
use Brick\Math\BigNumber;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Password;
use IteratorAggregate;

class RuleSet implements Arrayable, IteratorAggregate
{
    public function __construct(protected array $rules = [])
    {
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rules);
    }

    /**
     * Get the rule set as an array.
     */
    public function toArray(): array
    {
        return $this->rules;
    }

    /**
     * Create a new rule set.
     */
    public static function create(array $rules = []): self
    {
        return new static($rules);
    }

    /**
     * Defines a rule set to be re-used later.
     */
    public static function define(string $name, RuleSet $ruleSet): void
    {
        static::getDefinedRuleSets()->define($name, $ruleSet);
    }

    /**
     * Uses a previously defined rule set.
     */
    public static function useDefined(string $name): RuleSet
    {
        return static::getDefinedRuleSets()->useDefined($name);
    }

    /**
     * Append one or more rules to the end of the rule set.
     *
     * @param \Illuminate\Contracts\Validation\Rule|string $rule
     */
    public function concat(...$rule): self
    {
        return static::create([...$this->rules, ...$rule]);
    }

    /**
     * Append all rules from a defined rule set.
     */
    public function concatDefined(string $name): self
    {
        return $this->concat(...static::useDefined($name)->toArray());
    }

    /**
     * Append a rule to the end of the rule set.
     *
     * @param \Illuminate\Contracts\Validation\Rule|string $rule
     */
    public function rule(mixed $rule): self
    {
        return static::create([...$this->rules, $rule]);
    }

    /**
     * The field under validation must be "yes", "on", 1, or true. This is useful for validating "Terms of Service"
     * acceptance or similar fields.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-accepted
     */
    public function accepted(): self
    {
        return $this->rule(Rule::accepted());
    }

    /**
     * The field under validation must be "yes", "on", 1, or true if *anotherField* under validation is equal to a
     * specified *value*. This is useful for validating "Terms of Service" acceptance or similar fields.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-accepted-if
     */
    public function acceptedIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::acceptedIf($anotherField, ...$value));
    }

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-active-url
     */
    public function activeUrl(): self
    {
        return $this->rule(Rule::activeUrl());
    }

    /**
     * The field under validation must be a value after a given date.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-after
     */
    public function after(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::after($date));
    }

    /**
     * The field under validation must be a value after or equal to the given date.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-after-or-equal
     */
    public function afterOrEqual(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::afterOrEqual($date));
    }

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-alpha
     */
    public function alpha(): self
    {
        return $this->rule(Rule::alpha());
    }

    /**
     * The field under validation may have alpha-numeric characters, as well as dashes and underscores.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-alpha-dash
     */
    public function alphaDash(): self
    {
        return $this->rule(Rule::alphaDash());
    }

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-alpha-num
     */
    public function alphaNum(): self
    {
        return $this->rule(Rule::alphaNum());
    }

    /**
     * The field under validation must be a PHP *array*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-array
     */
    public function array(string ...$requiredKey): self
    {
        return $this->rule(Rule::array(...$requiredKey));
    }

    /**
     * The field under validation must be entirely 7-bit ASCII characters.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-ascii
     */
    public function ascii(): self
    {
        return $this->rule(Rule::ascii());
    }

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-bail
     */
    public function bail(): self
    {
        return $this->rule(Rule::bail());
    }

    /**
     * The field under validation must be a value preceding the given date.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-before
     */
    public function before(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::before($date));
    }

    /**
     * The field under validation must be a value preceding or equal to the given date.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-before-or-equal
     */
    public function beforeOrEqual(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::beforeOrEqual($date));
    }

    /**
     * The field under validation must have a size between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-between
     */
    public function between(float|int|string|BigNumber $min, float|int|string|BigNumber $max): self
    {
        return $this->rule(Rule::between($min, $max));
    }

    /**
     * The field under validation must be able to be cast as boolean.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-boolean
     */
    public function boolean(): self
    {
        return $this->rule(Rule::boolean());
    }

    /**
     * The field under validation must pass a Gate check for the specified ability.
     *
     * @link https://laravel.com/docs/10.x/authorization#gates
     */
    public function can(string $ability, ...$arguments): self
    {
        return $this->rule(Rule::can($ability, ...$arguments));
    }

    /**
     * The field under validation must have a matching field of *{field}_confirmation*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-confirmed
     */
    public function confirmed(): self
    {
        return $this->rule(Rule::confirmed());
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-current-password
     */
    public function currentPassword(?string $authenticationGuard = null): self
    {
        return $this->rule(Rule::currentPassword($authenticationGuard));
    }

    /**
     * The field under validation must be a valid, non-relative date according to the 'strtotime' PHP function.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-date
     */
    public function date(): self
    {
        return $this->rule(Rule::date());
    }

    /**
     * The field under validation must be equal to the given date.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-date-equals
     */
    public function dateEquals(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::dateEquals($date));
    }

    /**
     * The field under validation must match the given *format*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     * @param string $dateFormat A format supported by the *DateTime* class
     */
    public function dateFormat(string $dateFormat): self
    {
        return $this->rule(Rule::dateFormat($dateFormat));
    }

    /**
     * The field under validation must be numeric and must contain the specified number of decimal places.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-decimal
     */
    public function decimal(int $precision, ?int $maxPrecision = null): self
    {
        return $this->rule(Rule::decimal($precision, $maxPrecision));
    }

    /**
     * The field under validation must be *"no"*, *"off"*, *0*, or *false*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-declined
     */
    public function declined(): self
    {
        return $this->rule(Rule::declined());
    }

    /**
     * The field under validation must be *"no"*, *"off"*, *0*, or *false* if *anotherField* under validation is equal
     * to a specified value.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-declined-if
     */
    public function declinedIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::declinedIf($anotherField, ...$value));
    }

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-different
     */
    public function different(string $field): self
    {
        return $this->rule(Rule::different($field));
    }

    /**
     * The field under validation must be numeric and must have an exact length of *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-digits
     */
    public function digits(int $count): self
    {
        return $this->rule(Rule::digits($count));
    }

    /**
     * The field under validation must be numeric and must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-digits-between
     */
    public function digitsBetween(int $min, int $max): self
    {
        return $this->rule(Rule::digitsBetween($min, $max));
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
     * If you would like to fluently define the rule, you may use {@see Rule::dimensions} with {@see RuleSet::rule} or
     * pass a callback which accepts a {@see \Illuminate\Validation\Rules\Dimensions} instance.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-dimensions
     */
    public function dimensions(array $constraints, ?callable $modifier = null): self
    {
        $rule = Rule::dimensions($constraints);

        if ($modifier) {
            $modifier($rule);
        }

        return $this->rule($rule);
    }

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-distinct
     */
    public function distinct(bool $strict = false, bool $ignoreCase = false): self
    {
        return $this->rule(Rule::distinct($strict, $ignoreCase));
    }

    /**
     * The field under validation must not end with one of the given values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-doesnt-end-with
     */
    public function doesntEndWith(string ...$value): self
    {
        return $this->rule(Rule::doesntEndWith(...$value));
    }

    /**
     * The field under validation must not start with one of the given values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-doesnt-start-with
     */
    public function doesntStartWith(string ...$value): self
    {
        return $this->rule(Rule::doesntStartWith(...$value));
    }

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-email
     */
    public function email(string ...$validator): self
    {
        return $this->rule(Rule::email(...$validator));
    }

    /**
     * The field under validation must end with one of the given values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-ends-with
     */
    public function endsWith(string ...$value): self
    {
        return $this->rule(Rule::endsWith(...$value));
    }

    /**
     * The field under validation contains a valid enum value of the specified type.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-enum
     * @param class-string $type
     */
    public function enum(string $type): self
    {
        return $this->rule(Rule::enum($type));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude
     */
    public function exclude(): self
    {
        return $this->rule(Rule::exclude());
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if a true boolean is passed in or the passed in closure returns true.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude-if
     * @param callable|bool $callback
     */
    public function excludeIf(mixed $callback): self
    {
        return $this->rule(Rule::excludeIf($callback));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*.
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude-if
     */
    public function excludeIfValue(string $anotherField, ?string $value): self
    {
        return $this->rule(Rule::excludeIfValue($anotherField, $value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude-unless
     */
    public function excludeUnless(string $anotherField, ?string $value): self
    {
        return $this->rule(Rule::excludeUnless($anotherField, $value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude-with
     */
    public function excludeWith(string $anotherField): self
    {
        return $this->rule(Rule::excludeWith($anotherField));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is not present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exclude-without
     */
    public function excludeWithout(string $anotherField): self
    {
        return $this->rule(Rule::excludeWithout($anotherField));
    }

    /**
     * The field under validation must exist in a given database table. If the *column* option is not specified, the
     * field name will be used. Instead of specifying the table name directly, you may specify the Eloquent model class
     * name.
     *
     * If you would like to customize the query executed by the validation rule, you may use {@see Rule::exists} with
     * {@see RuleSet::rule} or pass a callback which accepts an {@see \Illuminate\Validation\Rules\Exists} instance.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-exists
     */
    public function exists(string $table, string $column = 'NULL', ?callable $modifier = null): self
    {
        $rule = Rule::exists($table, $column);

        if ($modifier) {
            $modifier($rule);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-file
     */
    public function file(): self
    {
        return $this->rule(Rule::file());
    }

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-filled
     */
    public function filled(): self
    {
        return $this->rule(Rule::filled());
    }

    /**
     * The field under validation must be greater than the given *field*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-gt
     */
    public function gt(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::gt($field));
    }

    /**
     * The field under validation must be greater than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-gte
     */
    public function gte(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::gte($field));
    }

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/10.x/validation#rule-image
     */
    public function image(): self
    {
        return $this->rule(Rule::image());
    }

    /**
     * The field under validation must be included in the given list of values.
     *
     * When the *in* rule is combined with the *array* rule, each value in the input array must be present within the
     * list of values provided to the *in* rule.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-in
     */
    public function in(Arrayable|array|string $values): self
    {
        return $this->rule(Rule::in($values));
    }

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-in-array
     */
    public function inArray(string $anotherField): self
    {
        return $this->rule(Rule::inArray($anotherField));
    }

    /**
     * The field under validation must be an integer.
     *
     * NOTE: This validation rule does not verify that the input is of the "integer" variable type, only that the input
     * is of a type accepted by PHP's FILTER_VALIDATE_INT rule.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-integer
     */
    public function integer(): self
    {
        return $this->rule(Rule::integer());
    }

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-ip
     */
    public function ip(): self
    {
        return $this->rule(Rule::ip());
    }

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/10.x/validation#ipv4
     */
    public function ipv4(): self
    {
        return $this->rule(Rule::ipv4());
    }

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/10.x/validation#ipv6
     */
    public function ipv6(): self
    {
        return $this->rule(Rule::ipv6());
    }

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-json
     */
    public function json(): self
    {
        return $this->rule(Rule::json());
    }

    /**
     * The field under validation must be lowercase.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-lowercase
     */
    public function lowercase(): self
    {
        return $this->rule(Rule::lowercase());
    }

    /**
     * The field under validation must be less than the given *field*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-lt
     */
    public function lt(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::lt($field));
    }

    /**
     * The field under validation must be less than or equal to the given *field*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-lte
     */
    public function lte(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::lte($field));
    }

    /**
     * The field under validation must be a MAC address.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-mac
     */
    public function macAddress(): self
    {
        return $this->rule(Rule::macAddress());
    }

    /**
     * The field under validation must be less than or equal to a maximum *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-max
     */
    public function max(BigNumber|int|float|string $value): self
    {
        return $this->rule(Rule::max($value));
    }

    /**
     * The integer under validation must have a maximum length of *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-max-digits
     */
    public function maxDigits(int $value): self
    {
        return $this->rule(Rule::maxDigits($value));
    }

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public function mimes(string ...$extension): self
    {
        return $this->rule(Rule::mimes(...$extension));
    }

    /**
     * The file under validation must match one of the given MIME types.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-mimetypes
     */
    public function mimetypes(string ...$mimeType): self
    {
        return $this->rule(Rule::mimetypes(...$mimeType));
    }

    /**
     * The field under validation must have a minimum *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-min
     */
    public function min(BigNumber|int|float|string $value): self
    {
        return $this->rule(Rule::min($value));
    }

    /**
     * The integer under validation must have a minimum length of *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-min-digits
     */
    public function minDigits(int $value): self
    {
        return $this->rule(Rule::minDigits($value));
    }

    /**
     * The field under validation must not be present in the input data.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-missing
     */
    public function missing(): self
    {
        return $this->rule(Rule::missing());
    }

    /**
     * The field under validation must not be present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-missing-if
     */
    public function missingIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::missingIf($anotherField, ...$value));
    }

    /**
     * The field under validation must not be present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-missing-unless
     */
    public function missingUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::missingUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must not be present *only if* any of the other specified fields are present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-missing-with
     */
    public function missingWith(string ...$field): self
    {
        return $this->rule(Rule::missingWith(...$field));
    }

    /**
     * The field under validation must not be present *only if* all of the other specified fields are present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-missing-with-all
     */
    public function missingWithAll(string ...$field): self
    {
        return $this->rule(Rule::missingWithAll(...$field));
    }

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#multiple-of
     */
    public function multipleOf(int|float $value): self
    {
        return $this->rule(Rule::multipleOf($value));
    }

    /**
     * The field under validation must not be included in the given list of values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-not-in
     */
    public function notIn(Arrayable|array|string $values): self
    {
        return $this->rule(Rule::notIn($values));
    }

    /**
     * The field under validation must not match the given regular expression.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-not-regex
     */
    public function notRegex(string $pattern): self
    {
        return $this->rule(Rule::notRegex($pattern));
    }

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-nullable
     */
    public function nullable(): self
    {
        return $this->rule(Rule::nullable());
    }

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-numeric
     * @link https://www.php.net/manual/en/function.is-numeric.php
     */
    public function numeric(): self
    {
        return $this->rule(Rule::numeric());
    }

    /**
     * The field under validation must be a string with an adequate level of complexity for a password. Defaults to a
     * minimum of 8 characters if no size is provided and {@see Password::defaults} was not used.
     *
     * If you would like to customize the password rule, you may use {@see Password::defaults} and pass no options,
     * use {@see Rule::password} with {@see RuleSet::rule}, or pass a callback which accepts a {@see Password} instance.
     *
     * @link https://laravel.com/docs/10.x/validation#validating-passwords
     */
    public function password(?int $size = null, ?callable $modifier = null): self
    {
        $rule = Rule::password($size);

        if ($modifier) {
            $modifier($rule);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-present
     */
    public function present(): self
    {
        return $this->rule(Rule::present());
    }

    /**
     * The field under validation must be present but can be empty if *anotherField* under validation is equal to a
     * specified value.
     */
    public function presentIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::presentIf($anotherField, ...$value));
    }

    /**
     * The field under validation must be present but can be empty unless the *anotherField* field is equal to any
     * *value*.
     */
    public function presentUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::presentUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must be present but can be empty *only if* any of the other specified fields are
     * present and not empty.
     */
    public function presentWith(string ...$field): self
    {
        return $this->rule(Rule::presentWith(...$field));
    }

    /**
     * The field under validation must be present but can be empty *only if* all the other specified fields are present
     * and not empty.
     */
    public function presentWithAll(string ...$field): self
    {
        return $this->rule(Rule::presentWithAll(...$field));
    }

    /**
     * The field under validation must be empty or not present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-prohibited
     */
    public function prohibited(): self
    {
        return $this->rule(Rule::prohibited());
    }

    /**
     * The field under validation must be empty or not present in the input data if a true boolean is passed in or the
     * passed in closure returns true.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-prohibited-if
     * @param callable|bool $callback
     */
    public function prohibitedIf(mixed $callback): self
    {
        return $this->rule(Rule::prohibitedIf($callback));
    }

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-prohibited-if
     */
    public function prohibitedIfValue(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::prohibitedIfValue($anotherField, ...$value));
    }

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-prohibited-unless
     */
    public function prohibitedUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::prohibitedUnless($anotherField, ...$value));
    }

    /**
     * If the field under validation is present, no fields in *anotherField* can be present, even if empty.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-prohibits
     */
    public function prohibits(string ...$anotherField): self
    {
        return $this->rule(Rule::prohibits(...$anotherField));
    }

    /**
     * The field under validation must match the given regular expression.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-regex
     */
    public function regex(string $pattern): self
    {
        return $this->rule(Rule::regex($pattern));
    }

    /**
     * The field under validation must be present in the input data and not empty.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required
     */
    public function required(): self
    {
        return $this->rule(Rule::required());
    }

    /**
     * The field under validation must be an array and must contain at least the specified keys.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-array-keys
     */
    public function requiredArrayKeys(string ...$key): self
    {
        return $this->rule(Rule::requiredArrayKeys(...$key));
    }

    /**
     * The field under validation must be present in the input data if a true boolean is passed in or the passed in
     * closure returns true.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-if
     * @param callable|bool $callback
     */
    public function requiredIf(mixed $callback): self
    {
        return $this->rule(Rule::requiredIf($callback));
    }

    /**
     * The field must be present if the other specified field is accepted.
     *
     * @see RuleSet::accepted() for accepted criteria
     */
    public function requiredIfAccepted(string $field): self
    {
        return $this->rule(Rule::requiredIfAccepted($field));
    }

    /**
     * The field must be present if all the criteria are true.
     */
    public function requiredIfAll(RequiredIf ...$rules): self
    {
        return $this->rule(Rule::requiredIfAll(...$rules));
    }

    /**
     * The field must be present if any of the criteria are true.
     */
    public function requiredIfAny(RequiredIf ...$rules): self
    {
        return $this->rule(Rule::requiredIfAny(...$rules));
    }

    /**
     * The field under validation must be present and not empty if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-if
     */
    public function requiredIfValue(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::requiredIfValue($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-unless
     */
    public function requiredUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::requiredUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-with
     */
    public function requiredWith(string ...$field): self
    {
        return $this->rule(Rule::requiredWith(...$field));
    }

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-with-all
     */
    public function requiredWithAll(string ...$field): self
    {
        return $this->rule(Rule::requiredWithAll(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-without
     */
    public function requiredWithout(string ...$field): self
    {
        return $this->rule(Rule::requiredWithout(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-required-without-all
     */
    public function requiredWithoutAll(string ...$field): self
    {
        return $this->rule(Rule::requiredWithoutAll(...$field));
    }

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-same
     */
    public function same(string $field): self
    {
        return $this->rule(Rule::same($field));
    }

    /**
     * The field under validation must have a size matching the given *value*.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-size
     */
    public function size(BigNumber|int|float|string $value): self
    {
        return $this->rule(Rule::size($value));
    }

    /**
     * The field under validation will be validated *only* if that field is present in the data.
     *
     * Note: Must be used with other rules to have any effect.
     *
     * @link https://laravel.com/docs/10.x/validation#validating-when-present
     */
    public function sometimes(): self
    {
        return $this->rule(Rule::sometimes());
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-starts-with
     */
    public function startsWith(string ...$value): self
    {
        return $this->rule(Rule::startsWith(...$value));
    }

    /**
     * The field under validation must be a string.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-string
     */
    public function string(): self
    {
        return $this->rule(Rule::string());
    }

    /**
     * The field under validation must be a valid timezone identifier according to the *timezone_identifiers_list* PHP
     * function.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-timezone
     */
    public function timezone(): self
    {
        return $this->rule(Rule::timezone());
    }

    /**
     * The field under validation must be a valid Universally Unique Lexicographically Sortable Identifier (ULID).
     *
     * @link https://laravel.com/docs/10.x/validation#rule-ulid
     * @link https://github.com/ulid/spec
     */
    public function ulid(): self
    {
        return $this->rule(Rule::ulid());
    }

    /**
     * The field under validation must not exist within the given database table. If the *column* option is not
     * specified, the field name will be used. Instead of specifying the table name directly, you may specify the
     * Eloquent model class name.
     *
     * If you would like to customize the query executed by the validation rule, you may use {@see Rule::unique} with
     * {@see RuleSet::rule} or pass a callback which accepts a {@see \Illuminate\Validation\Rules\Unique} instance.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-unique
     */
    public function unique(string $table, string $column = 'NULL', ?callable $modifier = null): self
    {
        $rule = Rule::unique($table, $column);

        if ($modifier) {
            $modifier($rule);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be uppercase.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-uppercase
     */
    public function uppercase(): self
    {
        return $this->rule(Rule::uppercase());
    }

    /**
     * The field under validation must be a valid URL.
     *
     * @link https://laravel.com/docs/10.x/validation#rule-url
     */
    public function url(): self
    {
        return $this->rule(Rule::url());
    }

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/10.x/validation#rule-uuid
     */
    public function uuid(): self
    {
        return $this->rule(Rule::uuid());
    }

    /**
     * Create a new conditional rule set.
     */
    public function when(mixed $condition, array|string|RuleSet $rules, array|string|RuleSet $defaultRules = []): self
    {
        return $this->rule(Rule::when($condition, $rules, $defaultRules));
    }

    protected static function getDefinedRuleSets(): Contracts\DefinedRuleSets
    {
        return resolve(Contracts\DefinedRuleSets::class);
    }
}
