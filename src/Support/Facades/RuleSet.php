<?php

declare(strict_types=1);

namespace Sourcetoad\RuleHelper\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * @method static \Sourcetoad\RuleHelper\RuleSet concat(...$rule)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredIfAny(RequiredIf ...$rules)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredIfAll(RequiredIf ...$rules)
 * @method static \Sourcetoad\RuleHelper\RuleSet accepted()
 * @method static \Sourcetoad\RuleHelper\RuleSet activeUrl()
 * @method static \Sourcetoad\RuleHelper\RuleSet after($date)
 * @method static \Sourcetoad\RuleHelper\RuleSet afterOrEqual($date)
 * @method static \Sourcetoad\RuleHelper\RuleSet alpha()
 * @method static \Sourcetoad\RuleHelper\RuleSet alphaDash()
 * @method static \Sourcetoad\RuleHelper\RuleSet alphaNum()
 * @method static \Sourcetoad\RuleHelper\RuleSet array(string ...$requiredKey)
 * @method static \Sourcetoad\RuleHelper\RuleSet bail()
 * @method static \Sourcetoad\RuleHelper\RuleSet before($date)
 * @method static \Sourcetoad\RuleHelper\RuleSet beforeOrEqual($date)
 * @method static \Sourcetoad\RuleHelper\RuleSet between(int $min, int $max)
 * @method static \Sourcetoad\RuleHelper\RuleSet boolean()
 * @method static \Sourcetoad\RuleHelper\RuleSet confirmed()
 * @method static \Sourcetoad\RuleHelper\RuleSet currentPassword(?string $authenticationGuard = null)
 * @method static \Sourcetoad\RuleHelper\RuleSet date()
 * @method static \Sourcetoad\RuleHelper\RuleSet dateEquals($date)
 * @method static \Sourcetoad\RuleHelper\RuleSet dateFormat(string $dateFormat)
 * @method static \Sourcetoad\RuleHelper\RuleSet different(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet digits(int $count)
 * @method static \Sourcetoad\RuleHelper\RuleSet digitsBetween(int $min, int $max)
 * @method static \Sourcetoad\RuleHelper\RuleSet distinct(bool $strict = false, $ignoreCase = false)
 * @method static \Sourcetoad\RuleHelper\RuleSet email(string ...$validator)
 * @method static \Sourcetoad\RuleHelper\RuleSet endsWith(string ...$value)
 * @method static \Sourcetoad\RuleHelper\RuleSet excludeIf(string $anotherField, ?string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet excludeUnless(string $anotherField, ?string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet file()
 * @method static \Sourcetoad\RuleHelper\RuleSet filled()
 * @method static \Sourcetoad\RuleHelper\RuleSet gt(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet gte(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet image()
 * @method static \Sourcetoad\RuleHelper\RuleSet inArray(string $anotherField)
 * @method static \Sourcetoad\RuleHelper\RuleSet integer()
 * @method static \Sourcetoad\RuleHelper\RuleSet ip()
 * @method static \Sourcetoad\RuleHelper\RuleSet ipv4()
 * @method static \Sourcetoad\RuleHelper\RuleSet ipv6()
 * @method static \Sourcetoad\RuleHelper\RuleSet json()
 * @method static \Sourcetoad\RuleHelper\RuleSet lt(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet lte(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet max(string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet mimes(string ...$extension)
 * @method static \Sourcetoad\RuleHelper\RuleSet mimetypes(string ...$mimeType)
 * @method static \Sourcetoad\RuleHelper\RuleSet min(string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet multipleOf(string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet notRegex(string $pattern)
 * @method static \Sourcetoad\RuleHelper\RuleSet nullable()
 * @method static \Sourcetoad\RuleHelper\RuleSet numeric()
 * @method static \Sourcetoad\RuleHelper\RuleSet password()
 * @method static \Sourcetoad\RuleHelper\RuleSet present()
 * @method static \Sourcetoad\RuleHelper\RuleSet prohibited()
 * @method static \Sourcetoad\RuleHelper\RuleSet prohibitedIf(string $anotherField, string ...$value)
 * @method static \Sourcetoad\RuleHelper\RuleSet prohibitedUnless(string $anotherField, string ...$value)
 * @method static \Sourcetoad\RuleHelper\RuleSet regex(string $pattern)
 * @method static \Sourcetoad\RuleHelper\RuleSet required()
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredUnless(string $anotherField, string ...$value)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredWith(string ...$field)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredWithAll(string ...$field)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredWithout(string ...$field)
 * @method static \Sourcetoad\RuleHelper\RuleSet requiredWithoutAll(string ...$field)
 * @method static \Sourcetoad\RuleHelper\RuleSet same(string $field)
 * @method static \Sourcetoad\RuleHelper\RuleSet size(string $value)
 * @method static \Sourcetoad\RuleHelper\RuleSet sometimes()
 * @method static \Sourcetoad\RuleHelper\RuleSet startsWith(string ...$value)
 * @method static \Sourcetoad\RuleHelper\RuleSet string()
 * @method static \Sourcetoad\RuleHelper\RuleSet timezone()
 * @method static \Sourcetoad\RuleHelper\RuleSet url()
 * @method static \Sourcetoad\RuleHelper\RuleSet uuid()
 */
class RuleSet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sourcetoad\RuleHelper\Contracts\RuleSet::class;
    }
}
