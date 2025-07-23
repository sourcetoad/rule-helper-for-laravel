<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper;

use ArrayIterator;
use BackedEnum;
use Brick\Math\BigNumber;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\RequiredIf;
use IteratorAggregate;
use UnitEnum;

/**
 * @phpstan-import-type RuleType from Rule
 * @phpstan-import-type RuleSetDefinition from Rule
 * @implements Arrayable<array-key, RuleType>
 * @implements IteratorAggregate<array-key, RuleType>
 */
class RuleSet implements Arrayable, IteratorAggregate
{
    /**
     * @param array<array-key, RuleType> $rules
     */
    final public function __construct(protected array $rules = [])
    {
        //
    }

    /**
     * @return ArrayIterator<array-key, RuleType>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rules);
    }

    /**
     * Get the rule set as an array.
     *
     * @return array<array-key, RuleType>
     */
    public function toArray(): array
    {
        return $this->rules;
    }

    /**
     * Create a new rule set.
     *
     * @param array<array-key, RuleType> $rules
     */
    public static function create(array $rules = []): self
    {
        return new static($rules);
    }

    /**
     * Defines a rule set to be re-used later.
     */
    public static function define(string|BackedEnum|UnitEnum $name, RuleSet $ruleSet): void
    {
        static::getDefinedRuleSets()->define($name, $ruleSet);
    }

    /**
     * Uses a previously defined rule set.
     */
    public static function useDefined(string|BackedEnum|UnitEnum $name): RuleSet
    {
        return static::getDefinedRuleSets()->useDefined($name);
    }

    /**
     * Append one or more rules to the end of the rule set.
     *
     * @param RuleType $rule
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
     * @param RuleType $rule
     */
    public function rule(mixed $rule): self
    {
        return static::create([...$this->rules, $rule]);
    }

    /**
     * The field under validation must be "yes", "on", 1, "1", true, or "true". This is useful for validating "Terms of
     * Service" acceptance or similar fields.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-accepted
     */
    public function accepted(): self
    {
        return $this->rule(Rule::accepted());
    }

    /**
     * The field under validation must be "yes", "on", 1, "1", true, or "true" if another field under validation is
     * equal to a specified value. This is useful for validating "Terms of Service" acceptance or similar fields.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-accepted-if
     */
    public function acceptedIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::acceptedIf($anotherField, ...$value));
    }

    /**
     * The field under validation must have a valid A or AAAA record according to the *dns_get_record* PHP function. The
     * hostname of the provided URL is extracted using the *parse_url* PHP function before being passed to
     * *dns_get_record*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-active-url
     */
    public function activeUrl(): self
    {
        return $this->rule(Rule::activeUrl());
    }

    /**
     * The field under validation must be a value after a given date. If a string is used, the dates will be passed into
     * the *strtotime* PHP function in order to be converted to a valid DateTime instance.
     *
     * Instead of passing a date string to be evaluated by *strtotime*, you may specify another field to compare against
     * the date.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-after
     */
    public function after(string|DateTimeInterface $dateOrField): self
    {
        return $this->rule(Rule::after($dateOrField));
    }

    /**
     * The field under validation must be a value after or equal to the given date. For more information, see the
     * {@see RuleSet::after} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-after-or-equal
     */
    public function afterOrEqual(string|DateTimeInterface $dateOrField): self
    {
        return $this->rule(Rule::afterOrEqual($dateOrField));
    }

    /**
     * The field under validation must be entirely Unicode alphabetic characters contained in *\p{L}* and *\p{M}*.
     *
     * To restrict this validation rule to characters in the ASCII range (*a-z* and *A-Z*), use the *limitToAscii*
     * argument.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-alpha
     */
    public function alpha(?bool $limitToAscii = null): self
    {
        return $this->rule(Rule::alpha($limitToAscii));
    }

    /**
     * The field under validation must be entirely Unicode alpha-numeric characters contained in *\p{L}*, *\p{M}*,
     * *\p{N}*, as well as ASCII dashes (*-*) and ASCII underscores (*_*).
     *
     * To restrict this validation rule to characters in the ASCII range (*a-z* and *A-Z*), use the *limitToAscii*
     * argument.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-alpha-dash
     */
    public function alphaDash(?bool $limitToAscii = null): self
    {
        return $this->rule(Rule::alphaDash($limitToAscii));
    }

    /**
     * The field under validation must be entirely Unicode alpha-numeric characters contained in *\p{L}*, *\p{M}*, and
     * *\p{N}*.
     *
     * To restrict this validation rule to characters in the ASCII range (*a-z* and *A-Z*), use the *limitToAscii*
     * argument.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-alpha-num
     */
    public function alphaNum(?bool $limitToAscii = null): self
    {
        return $this->rule(Rule::alphaNum($limitToAscii));
    }

    /**
     * The `anyOf` validation rule allows you to specify that the field under validation must satisfy any of the given
     * validation rulesets.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-anyof
     * @param array<array-key, RuleSetDefinition> $ruleSets
     */
    public function anyOf(array $ruleSets): self
    {
        return $this->rule(Rule::anyOf($ruleSets));
    }

    /**
     * The field under validation must be a PHP *array*.
     *
     * When additional values are provided to the *array* rule, each key in the input array must be present within the
     * list of values provided to the rule.
     *
     * In general, you should always specify the array keys that are allowed to be present within your array.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-array
     */
    public function array(BackedEnum|UnitEnum|string ...$requiredKey): self
    {
        return $this->rule(Rule::array(...$requiredKey));
    }

    /**
     * The field under validation must be entirely 7-bit ASCII characters.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-ascii
     */
    public function ascii(): self
    {
        return $this->rule(Rule::ascii());
    }

    /**
     * Stop running validation rules for the field after the first validation failure.
     *
     * While the bail rule will only stop validating a specific field when it encounters a validation failure, the
     * *stopOnFirstFailure method will inform the validator that it should stop validating all attributes once a single
     * validation failure has occurred.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-bail
     */
    public function bail(): self
    {
        return $this->rule(Rule::bail());
    }

    /**
     * The field under validation must be a value preceding the given date. The dates will be passed into the PHP
     * *strtotime* function in order to be converted into a valid *DateTime* instance. In addition, like the
     * {@see RuleSet::after} rule, the name of another field under validation may be supplied as the value of date.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-before
     */
    public function before(string|DateTimeInterface $dateOrField): self
    {
        return $this->rule(Rule::before($dateOrField));
    }

    /**
     * The field under validation must be a value preceding or equal to the given date. The dates will be passed into
     * the PHP *strtotime* function in order to be converted into a valid *DateTime* instance. In addition, like the
     * {@see RuleSet::after} rule, the name of another field under validation may be supplied as the value of date.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-before-or-equal
     */
    public function beforeOrEqual(string|DateTimeInterface $dateOrField): self
    {
        return $this->rule(Rule::beforeOrEqual($dateOrField));
    }

    /**
     * The field under validation must have a size between the given *min* and *max* (inclusive). Strings, numerics,
     * arrays, and files are evaluated in the same fashion as the {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-between
     */
    public function between(float|int|string|BigNumber $min, float|int|string|BigNumber $max): self
    {
        return $this->rule(Rule::between($min, $max));
    }

    /**
     * The field under validation must be able to be cast as a boolean. Accepted input are true, false, 1, 0, "1", and
     * "0".
     *
     * @link https://laravel.com/docs/12.x/validation#rule-boolean
     */
    public function boolean(bool $strict = false): self
    {
        return $this->rule(Rule::boolean($strict));
    }

    /**
     * The field under validation must pass a Gate check for the specified ability.
     *
     * @link https://laravel.com/docs/12.x/authorization#gates
     */
    public function can(string $ability, mixed ...$arguments): self
    {
        return $this->rule(Rule::can($ability, ...$arguments));
    }

    /**
     * The field under validation must have a matching field of *{field}_confirmation*. For example, if the field under
     * validation is *password*, a matching *password_confirmation* field must be present in the input.
     *
     * You may also pass a custom confirmation field name. For example, passing *repeat_username* will expect the field
     * *repeat_username* to match the field under validation.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-confirmed
     */
    public function confirmed(?string $confirmationFieldName = null): self
    {
        return $this->rule(Rule::confirmed($confirmationFieldName));
    }

    /**
     * The field under validation must be an array that contains all of the given parameter values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-contains
     */
    public function contains(mixed ...$value): self
    {
        return $this->rule(Rule::contains(...$value));
    }

    /**
     * The field under validation must match the authenticated user's password.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-current-password
     */
    public function currentPassword(?string $authenticationGuard = null): self
    {
        return $this->rule(Rule::currentPassword($authenticationGuard));
    }

    /**
     * The field under validation must be a valid, non-relative date according to the *strtotime* PHP function.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-date
     * @param ?callable(\Illuminate\Validation\Rules\Date): (\Illuminate\Validation\Rules\Date|void) $modifier
     */
    public function date(?callable $modifier = null): self
    {
        $rule = Rule::date();

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be equal to the given date. The dates will be passed into the PHP *strtotime*
     * function in order to be converted into a valid *DateTime* instance.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-date-equals
     */
    public function dateEquals(string|DateTimeInterface $date): self
    {
        return $this->rule(Rule::dateEquals($date));
    }

    /**
     * The field under validation must match one of the given formats. You should use **either** *date* or *dateFormat*
     * when validating a field, not both. This validation rule supports all formats supported by PHP's *DateTime* class.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-date-format
     * @link https://www.php.net/manual/en/datetime.format.php
     */
    public function dateFormat(string ...$dateFormat): self
    {
        return $this->rule(Rule::dateFormat(...$dateFormat));
    }

    /**
     * The field under validation must be numeric and must contain the specified number of decimal places.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-decimal
     */
    public function decimal(int $precision, ?int $maxPrecision = null): self
    {
        return $this->rule(Rule::decimal($precision, $maxPrecision));
    }

    /**
     * The field under validation must be "no", "off", 0, or false.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-declined
     */
    public function declined(): self
    {
        return $this->rule(Rule::declined());
    }

    /**
     * The field under validation must be "no", "off", 0, "0", false, or "false" if another field under validation is
     * equal to a specified value.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-declined-if
     */
    public function declinedIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::declinedIf($anotherField, ...$value));
    }

    /**
     * The field under validation must have a different value than *field*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-different
     */
    public function different(string $field): self
    {
        return $this->rule(Rule::different($field));
    }

    /**
     * The integer under validation must have the exact length of the given value.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-digits
     */
    public function digits(int $count): self
    {
        return $this->rule(Rule::digits($count));
    }

    /**
     * The integer validation must have a length between the given *min* and *max*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-digits-between
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
     * @link https://laravel.com/docs/12.x/validation#rule-dimensions
     * @param array<string, int|float|string> $constraints
     * @param ?callable(\Illuminate\Validation\Rules\Dimensions): (\Illuminate\Validation\Rules\Dimensions|void) $modifier
     */
    public function dimensions(array $constraints = [], ?callable $modifier = null): self
    {
        $rule = Rule::dimensions($constraints);

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * When validating arrays, the field under validation must not have any duplicate values.
     *
     * Distinct uses loose variable and case-sensitive comparisons by default. To use strict comparisons, or to ignore
     * the case of the values, use the *strict* or *ignoreCase* parameters.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-distinct
     */
    public function distinct(bool $strict = false, bool $ignoreCase = false): self
    {
        return $this->rule(Rule::distinct($strict, $ignoreCase));
    }

    /**
     * The field under validation must not end with one of the given values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-doesnt-end-with
     */
    public function doesntEndWith(string ...$value): self
    {
        return $this->rule(Rule::doesntEndWith(...$value));
    }

    /**
     * The field under validation must not start with one of the given values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-doesnt-start-with
     */
    public function doesntStartWith(string ...$value): self
    {
        return $this->rule(Rule::doesntStartWith(...$value));
    }

    /**
     * The field under validation must be formatted as an email address.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-email
     * @param ?callable(\Illuminate\Validation\Rules\Email): (\Illuminate\Validation\Rules\Email|void) $modifier
     */
    public function email(?callable $modifier = null): self
    {
        $rule = Rule::email();

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must end with one of the given values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-ends-with
     */
    public function endsWith(string ...$value): self
    {
        return $this->rule(Rule::endsWith(...$value));
    }

    /**
     * The field under validation contains a valid enum value of the specified type. When validating primitive values,
     * a backed Enum should be provided to the Enum rule.
     *
     * The Enum rule's *only* and *except* methods may be used to limit which enum cases should be considered valid.
     *
     * If you would like to fluently define the rule, you may use {@see Rule::enum} with {@see RuleSet::rule} or
     * pass a callback which accepts a {@see \Illuminate\Validation\Rules\Enum} instance.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-enum
     * @param class-string $type
     * @param ?callable(\Illuminate\Validation\Rules\Enum): (\Illuminate\Validation\Rules\Enum|void) $modifier
     */
    public function enum(string $type, ?callable $modifier = null): self
    {
        $rule = Rule::enum($type);

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude
     */
    public function exclude(): self
    {
        return $this->rule(Rule::exclude());
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if a true boolean is passed in or the passed in closure returns true.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude-if
     * @param bool|callable(): bool $callback
     */
    public function excludeIf(mixed $callback): self
    {
        return $this->rule(Rule::excludeIf($callback));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*.
     * methods if the *anotherField* field is equal to *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude-if
     */
    public function excludeIfValue(string $anotherField, ?string $value): self
    {
        return $this->rule(Rule::excludeIfValue($anotherField, $value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods unless *anotherField*'s field is equal to *value*. If value is *null*, the field under validation will be
     * excluded unless the comparison field is *null* or the comparison field is missing from the request data.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude-unless
     */
    public function excludeUnless(string $anotherField, ?string $value): self
    {
        return $this->rule(Rule::excludeUnless($anotherField, $value));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude-with
     */
    public function excludeWith(string $anotherField): self
    {
        return $this->rule(Rule::excludeWith($anotherField));
    }

    /**
     * The field under validation will be excluded from the request data returned by the *validate* and *validated*
     * methods if the *anotherField* field is not present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exclude-without
     */
    public function excludeWithout(string $anotherField): self
    {
        return $this->rule(Rule::excludeWithout($anotherField));
    }

    /**
     * The field under validation must exist in a given database table.
     *
     * If the *column* option is not specified, the field name will be used.
     *
     * Occasionally, you may need to specify a specific database connection to be used for the exists query. You can
     * accomplish this by prepending the connection name to the table name: `connection.table`.
     *
     * Instead of specifying the table name directly, you may specify the Eloquent model which should be used to
     * determine the table name.
     *
     * If you would like to customize the query executed by the validation rule, you may use {@see Rule::exists} with
     * {@see RuleSet::rule} or pass a callback which accepts an {@see \Illuminate\Validation\Rules\Exists} instance.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-exists
     * @param ?callable(\Illuminate\Validation\Rules\Exists): (\Illuminate\Validation\Rules\Exists|void) $modifier
     */
    public function exists(string $table, string $column = 'NULL', ?callable $modifier = null): self
    {
        $rule = Rule::exists($table, $column);

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The file under validation must have a user-assigned extension corresponding to one of the listed extensions.
     *
     * Warning: You should never rely on validating a file by its user-assigned extension alone. This rule should
     *          typically always be used in combination with the {@see RuleSet::mimes} or {@see RuleSet::mimetypes}
     *          rules.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-extensions
     */
    public function extensions(string ...$extension): self
    {
        return $this->rule(Rule::extensions(...$extension));
    }

    /**
     * The field under validation must be a successfully uploaded file.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-file
     * @param ?callable(\Illuminate\Validation\Rules\File): (\Illuminate\Validation\Rules\File|void) $modifier
     */
    public function file(?callable $modifier = null): self
    {
        $rule = Rule::file();

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must not be empty when it is present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-filled
     */
    public function filled(): self
    {
        return $this->rule(Rule::filled());
    }

    /**
     * The field under validation must be greater than the given *field* or *value*. The two fields must be of the same
     * type. Strings, numerics, arrays, and files are evaluated using the same conventions as the {@see RuleSet::size}
     * rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-gt
     */
    public function gt(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::gt($field));
    }

    /**
     * The field under validation must be greater than or equal to the given *field* or *value*. The two fields must be
     * of the same type. Strings, numerics, arrays, and files are evaluated using the same conventions as the
     * {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-gte
     */
    public function gte(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::gte($field));
    }

    /**
     * The field under validation must contain a valid color value in hexadecimal format.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-hex-color
     * @link https://developer.mozilla.org/en-US/docs/Web/CSS/hex-color
     */
    public function hexColor(): self
    {
        return $this->rule(Rule::hexColor());
    }

    /**
     * The file under validation must be an image (jpg, jpeg, png, bmp, gif, svg, or webp).
     *
     * @link https://laravel.com/docs/12.x/validation#rule-image
     * @param ?callable(\Illuminate\Validation\Rules\ImageFile): (\Illuminate\Validation\Rules\ImageFile|void) $modifier
     */
    public function image(?callable $modifier = null): self
    {
        $rule = Rule::image();

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be included in the given list of values.
     *
     * When the *in* rule is combined with the {@see RuleSet::array} rule, each value in the input array must be present
     * within the list of values provided to the *in* rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-in
     * @param Arrayable<array-key, BackedEnum|UnitEnum|string>|array<BackedEnum|UnitEnum|string>|BackedEnum|UnitEnum|string $values
     */
    public function in(Arrayable|BackedEnum|UnitEnum|array|string $values): self
    {
        return $this->rule(Rule::in($values));
    }

    /**
     * The field under validation must exist in *anotherField*'s values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-in-array
     */
    public function inArray(string $anotherField): self
    {
        return $this->rule(Rule::inArray($anotherField));
    }

    /**
     * The field under validation must be an array having at least one of the given *values* as a key within the array.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-in-array-keys
     */
    public function inArrayKeys(string ...$value): self
    {
        return $this->rule(Rule::inArrayKeys(...$value));
    }

    /**
     * The field under validation must be an integer.
     *
     * Warning: This validation rule does not verify that the input is of the "integer" variable type, only that the
     *          input is of a type accepted by PHP's *FILTER_VALIDATE_INT* rule. If you need to validate the input as
     *          being a number please use this rule in combination with the {@see RuleSet::numeric} validation rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-integer
     */
    public function integer(): self
    {
        return $this->rule(Rule::integer());
    }

    /**
     * The field under validation must be an IP address.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-ip
     */
    public function ip(): self
    {
        return $this->rule(Rule::ip());
    }

    /**
     * The field under validation must be an IPv4 address.
     *
     * @link https://laravel.com/docs/12.x/validation#ipv4
     */
    public function ipv4(): self
    {
        return $this->rule(Rule::ipv4());
    }

    /**
     * The field under validation must be an IPv6 address.
     *
     * @link https://laravel.com/docs/12.x/validation#ipv6
     */
    public function ipv6(): self
    {
        return $this->rule(Rule::ipv6());
    }

    /**
     * The field under validation must be a valid JSON string.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-json
     */
    public function json(): self
    {
        return $this->rule(Rule::json());
    }

    /**
     * The field under validation must be an array that is a list. An array is considered a list if its keys consist of
     * consecutive numbers from *0* to *count($array) - 1*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-list
     */
    public function list(): self
    {
        return $this->rule(Rule::list());
    }

    /**
     * The field under validation must be lowercase.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-lowercase
     */
    public function lowercase(): self
    {
        return $this->rule(Rule::lowercase());
    }

    /**
     * The field under validation must be less than the given field. The two fields must be of the same type. Strings,
     * numerics, arrays, and files are evaluated using the same conventions as the {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-lt
     */
    public function lt(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::lt($field));
    }

    /**
     * The field under validation must be less than or equal to the given field. The two fields must be of the same
     * type. Strings, numerics, arrays, and files are evaluated using the same conventions as the
     * {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-lte
     */
    public function lte(BigNumber|int|float|string $field): self
    {
        return $this->rule(Rule::lte($field));
    }

    /**
     * The field under validation must be a MAC address.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-mac
     */
    public function macAddress(): self
    {
        return $this->rule(Rule::macAddress());
    }

    /**
     * The field under validation must be less than or equal to a maximum value. Strings, numerics, arrays, and files
     * are evaluated in the same fashion as the {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-max
     */
    public function max(BigNumber|int|float|string $value): self
    {
        return $this->rule(Rule::max($value));
    }

    /**
     * The integer under validation must have a maximum length of *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-max-digits
     */
    public function maxDigits(int $value): self
    {
        return $this->rule(Rule::maxDigits($value));
    }

    /**
     * The file under validation must have a MIME type corresponding to one of the listed extensions.
     *
     * Even though you only need to specify the extensions, this rule actually validates the MIME type of the file by
     * reading the file's contents and guessing its MIME type.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-mimes
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    public function mimes(string ...$extension): self
    {
        return $this->rule(Rule::mimes(...$extension));
    }

    /**
     * The file under validation must match one of the given MIME types.
     *
     * To determine the MIME type of the uploaded file, the file's contents will be read and the framework will attempt
     * to guess the MIME type, which may be different from the client's provided MIME type.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-mimetypes
     */
    public function mimetypes(string ...$mimeType): self
    {
        return $this->rule(Rule::mimetypes(...$mimeType));
    }

    /**
     * The field under validation must have a minimum value. Strings, numerics, arrays, and files are evaluated in the
     * same fashion as the {@see RuleSet::size} rule.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-min
     */
    public function min(BigNumber|int|float|string $value): self
    {
        return $this->rule(Rule::min($value));
    }

    /**
     * The integer under validation must have a minimum length of *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-min-digits
     */
    public function minDigits(int $value): self
    {
        return $this->rule(Rule::minDigits($value));
    }

    /**
     * The field under validation must not be present in the input data.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-missing
     */
    public function missing(): self
    {
        return $this->rule(Rule::missing());
    }

    /**
     * The field under validation must not be present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-missing-if
     */
    public function missingIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::missingIf($anotherField, ...$value));
    }

    /**
     * The field under validation must not be present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-missing-unless
     */
    public function missingUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::missingUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must not be present *only if* any of the other specified fields are present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-missing-with
     */
    public function missingWith(string ...$field): self
    {
        return $this->rule(Rule::missingWith(...$field));
    }

    /**
     * The field under validation must not be present *only if* all of the other specified fields are present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-missing-with-all
     */
    public function missingWithAll(string ...$field): self
    {
        return $this->rule(Rule::missingWithAll(...$field));
    }

    /**
     * The field under validation must be a multiple of *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-multiple-of
     */
    public function multipleOf(int|float $value): self
    {
        return $this->rule(Rule::multipleOf($value));
    }

    /**
     * The field under validation must not be included in the given list of values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-not-in
     * @param Arrayable<array-key, BackedEnum|UnitEnum|string>|array<BackedEnum|UnitEnum|string>|BackedEnum|UnitEnum|string $values
     */
    public function notIn(Arrayable|BackedEnum|UnitEnum|array|string $values): self
    {
        return $this->rule(Rule::notIn($values));
    }

    /**
     * The field under validation must not match the given regular expression.
     *
     * Internally, this rule uses the PHP *preg_match* function. The pattern specified should obey the same formatting
     * required by *preg_match* and thus also include valid delimiters.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-not-regex
     */
    public function notRegex(string $pattern): self
    {
        return $this->rule(Rule::notRegex($pattern));
    }

    /**
     * The field under validation may be *null*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-nullable
     */
    public function nullable(): self
    {
        return $this->rule(Rule::nullable());
    }

    /**
     * The field under validation must be numeric.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-numeric
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
     * @link https://laravel.com/docs/12.x/validation#validating-passwords
     * @param ?callable(\Illuminate\Validation\Rules\Password): (\Illuminate\Validation\Rules\Password|void) $modifier
     */
    public function password(?int $size = null, ?callable $modifier = null): self
    {
        $rule = Rule::password($size);

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be present in the input data but can be empty.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-present
     */
    public function present(): self
    {
        return $this->rule(Rule::present());
    }

    /**
     * The field under validation must be present if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-present-if
     */
    public function presentIf(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::presentIf($anotherField, ...$value));
    }

    /**
     * The field under validation must be present unless the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-present-unless
     */
    public function presentUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::presentUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must be present *only if* any of the other specified fields are present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-present-with
     */
    public function presentWith(string ...$field): self
    {
        return $this->rule(Rule::presentWith(...$field));
    }

    /**
     * The field under validation must be present *only if* all the other specified fields are present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-present-with-all
     */
    public function presentWithAll(string ...$field): self
    {
        return $this->rule(Rule::presentWithAll(...$field));
    }

    /**
     * The field under validation must be missing or empty. A field is "empty" if it meets one of the following
     * criteria:
     *  - The value is *null*.
     *  - The value is an empty string.
     *  - The value is an empty array or empty *Countable* object.
     *  - The value is an uploaded file with no path.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-prohibited
     */
    public function prohibited(): self
    {
        return $this->rule(Rule::prohibited());
    }

    /**
     * The field under validation must be empty or not present in the input data if a true boolean is passed in or the
     * passed in closure returns true.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-prohibited-if
     * @param bool|callable(): bool $callback
     */
    public function prohibitedIf(mixed $callback): self
    {
        return $this->rule(Rule::prohibitedIf($callback));
    }

    /**
     * The field under validation must be empty or not present in the input data if the *anotherField* field
     * is equal to "yes", "on", 1,  "1", true, or "true".
     */
    public function prohibitedIfAccepted(string $anotherField): self
    {
        return $this->rule(Rule::prohibitedIfAccepted($anotherField));
    }

    /**
     * The field under validation must be empty or not present in the input data if the *anotherField* field
     * is equal to "no", "off", 0, "0", false, or "false".
     */
    public function prohibitedIfDeclined(string $anotherField): self
    {
        return $this->rule(Rule::prohibitedIfDeclined($anotherField));
    }

    /**
     * The field under validation must be empty or not present if the *anotherField* field is equal to any *value*.
     *  - The value is *null*.
     *  - The value is an empty string.
     *  - The value is an empty array or empty *Countable* object.
     *  - The value is an uploaded file with no path.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-prohibited-if
     */
    public function prohibitedIfValue(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::prohibitedIfValue($anotherField, ...$value));
    }

    /**
     * The field under validation must be empty or not present unless the *anotherField* field is equal to any *value*.
     * A field is "empty" if it meets one of the following criteria:
     *  - The value is *null*.
     *  - The value is an empty string.
     *  - The value is an empty array or empty *Countable* object.
     *  - The value is an uploaded file with no path.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-prohibited-unless
     */
    public function prohibitedUnless(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::prohibitedUnless($anotherField, ...$value));
    }

    /**
     * If the field under validation is present, no fields in *anotherField* can be present, even if empty. A field is
     * "empty" if it meets one of the following criteria:
     *  - The value is *null*.
     *  - The value is an empty string.
     *  - The value is an empty array or empty *Countable* object.
     *  - The value is an uploaded file with no path.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-prohibits
     */
    public function prohibits(string ...$anotherField): self
    {
        return $this->rule(Rule::prohibits(...$anotherField));
    }

    /**
     * The field under validation must match the given regular expression.
     *
     * Internally, this rule uses the PHP *preg_match* function. The pattern specified should obey the same formatting
     * required by *preg_match* and thus also include valid delimiters.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-regex
     */
    public function regex(string $pattern): self
    {
        return $this->rule(Rule::regex($pattern));
    }

    /**
     * The field under validation must be present in the input data and not empty. A field is "empty" if it meets one of
     * the following criteria:
     *  - The value is *null*.
     *  - The value is an empty string.
     *  - The value is an empty array or empty *Countable* object.
     *  - The value is an uploaded file with no path.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required
     */
    public function required(): self
    {
        return $this->rule(Rule::required());
    }

    /**
     * The field under validation must be an array and must contain at least the specified keys.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-array-keys
     */
    public function requiredArrayKeys(string ...$key): self
    {
        return $this->rule(Rule::requiredArrayKeys(...$key));
    }

    /**
     * The field under validation must be present in the input data if a true boolean is passed in or the passed in
     * closure returns true.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-if
     * @param bool|callable(): bool $callback
     */
    public function requiredIf(mixed $callback): self
    {
        return $this->rule(Rule::requiredIf($callback));
    }

    /**
     * The field under validation must be present and not empty if the *field* field is equal to "yes", "on", 1,  "1",
     * true, or "true".
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-if-accepted
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
     * The field under validation must be present and not empty if the *field* field is equal to "no", "off", 0, "0",
     * false, or "false".
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-if-declined
     */
    public function requiredIfDeclined(string $field): self
    {
        return $this->rule(Rule::requiredIfDeclined($field));
    }

    /**
     * The field under validation must be present and not empty if the *anotherField* field is equal to any *value*.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-if
     */
    public function requiredIfValue(string $anotherField, string ...$value): self
    {
        return $this->rule(Rule::requiredIfValue($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty unless the *anotherField* field is equal to any
     * *value*. This also means *anotherField* must be present in the request data unless value is *null*. If value is
     * *null*, the field under validation will be required unless the comparison field is null or the comparison field
     * is missing from the request data.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-unless
     */
    public function requiredUnless(string $anotherField, ?string ...$value): self
    {
        return $this->rule(Rule::requiredUnless($anotherField, ...$value));
    }

    /**
     * The field under validation must be present and not empty *only if* any of the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-with
     */
    public function requiredWith(string ...$field): self
    {
        return $this->rule(Rule::requiredWith(...$field));
    }

    /**
     * The field under validation must be present and not empty *only if* all the other specified fields are present
     * and not empty.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-with-all
     */
    public function requiredWithAll(string ...$field): self
    {
        return $this->rule(Rule::requiredWithAll(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* any of the other specified fields are empty
     * or not present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-without
     */
    public function requiredWithout(string ...$field): self
    {
        return $this->rule(Rule::requiredWithout(...$field));
    }

    /**
     * The field under validation must be present and not empty *only when* all the other specified fields are empty or
     * not present.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-required-without-all
     */
    public function requiredWithoutAll(string ...$field): self
    {
        return $this->rule(Rule::requiredWithoutAll(...$field));
    }

    /**
     * The given *field* must match the field under validation.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-same
     */
    public function same(string $field): self
    {
        return $this->rule(Rule::same($field));
    }

    /**
     * The field under validation must have a size matching the given *value*.
     *  - For string data, value corresponds to the number of characters.
     *  - For numeric data, value corresponds to a given integer value (the attribute must also have the numeric or
     *    integer rule).
     *  - For an array, size corresponds to the count of the array.
     *  - For files, size corresponds to the file size in kilobytes.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-size
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
     * @link https://laravel.com/docs/12.x/validation#validating-when-present
     */
    public function sometimes(): self
    {
        return $this->rule(Rule::sometimes());
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-starts-with
     */
    public function startsWith(string ...$value): self
    {
        return $this->rule(Rule::startsWith(...$value));
    }

    /**
     * The field under validation must be a string. If you would like to allow the field to also be *null*, you should
     * assign the {@see RuleSet::nullable} rule to the field.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-string
     */
    public function string(): self
    {
        return $this->rule(Rule::string());
    }

    /**
     * The field under validation must be a valid timezone identifier according to the
     * {@see DateTimeZone::listIdentifiers} method.
     *
     * @param ?string $timezoneGroup One of the {@see DateTimeZone} class constant names.
     * @param ?string $countryCode A two-letter (uppercase) ISO 3166-1 compatible country code. Note: This option is only used when timezoneGroup is set to "per_country".
     * @link https://laravel.com/docs/12.x/validation#rule-timezone
     * @link https://www.php.net/manual/en/datetimezone.listidentifiers.php
     */
    public function timezone(?string $timezoneGroup = null, ?string $countryCode = null): self
    {
        return $this->rule(Rule::timezone($timezoneGroup, $countryCode));
    }

    /**
     * The field under validation must be a valid Universally Unique Lexicographically Sortable Identifier (ULID).
     *
     * @link https://laravel.com/docs/12.x/validation#rule-ulid
     * @link https://github.com/ulid/spec
     */
    public function ulid(): self
    {
        return $this->rule(Rule::ulid());
    }

    /**
     * The field under validation must not exist within the given database table.
     *
     * If the *column* option is not specified, the name of the field under validation will be used.
     *
     * Occasionally, you may need to specify a specific database connection to be used for the exists query. You can
     * accomplish this by prepending the connection name to the table name: `connection.table`.
     *
     * Instead of specifying the table name directly, you may specify the Eloquent model which should be used to
     * determine the table name.
     *
     * If you would like to customize the query executed by the validation rule, you may use {@see Rule::unique} with
     * {@see RuleSet::rule} or pass a callback which accepts a {@see \Illuminate\Validation\Rules\Unique} instance.
     *
     * Warning: You should never pass any user controlled request input into the *ignore* method. Instead, you should
     *          only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model
     *          instance. Otherwise, your application will be vulnerable to an SQL injection attack.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-unique
     * @param ?callable(\Illuminate\Validation\Rules\Unique): (\Illuminate\Validation\Rules\Unique|void) $modifier
     */
    public function unique(string $table, string $column = 'NULL', ?callable $modifier = null): self
    {
        $rule = Rule::unique($table, $column);

        if ($modifier) {
            $rule = $this->modify($rule, $modifier);
        }

        return $this->rule($rule);
    }

    /**
     * The field under validation must be uppercase.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-uppercase
     */
    public function uppercase(): self
    {
        return $this->rule(Rule::uppercase());
    }

    /**
     * The field under validation must be a valid URL. If no protocol is specified, all protocols are considered valid.
     *
     * @link https://laravel.com/docs/12.x/validation#rule-url
     */
    public function url(string ...$protocol): self
    {
        return $this->rule(Rule::url(...$protocol));
    }

    /**
     * The field under validation must be a valid RFC 4122 (version 1, 3, 4, or 5) universally unique identifier (UUID).
     *
     * @link https://laravel.com/docs/12.x/validation#rule-uuid
     */
    public function uuid(): self
    {
        return $this->rule(Rule::uuid());
    }

    /**
     * Create a new conditional rule set.
     *
     * @param bool|callable(\Illuminate\Support\Fluent<array-key, mixed>): bool $condition
     * @param array<array-key, RuleType>|string|RuleSet $rules
     * @param array<array-key, RuleType>|string|RuleSet $defaultRules
     */
    public function when(mixed $condition, array|string|RuleSet $rules, array|string|RuleSet $defaultRules = []): self
    {
        return $this->rule(Rule::when($condition, $rules, $defaultRules));
    }

    protected static function getDefinedRuleSets(): Contracts\DefinedRuleSets
    {
        return resolve(Contracts\DefinedRuleSets::class);
    }

    /**
     * @param T $rule
     * @param callable(T): (T|void) $modifier
     * @return T
     * @template T
     */
    protected function modify($rule, callable $modifier)
    {
        /** @var T */
        return $modifier($rule) ?: $rule;
    }
}
