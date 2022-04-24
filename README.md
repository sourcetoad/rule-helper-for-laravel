# Rule Helper for Laravel

Adds helpers to make building Laravel rule arrays easier by providing helper methods for the built-in rules.

## Installation

```shell
$ composer require sourcetoad/rule-helper-for-laravel
```

## Usage

### RuleSet

The `RuleSet` class provides a fluent interface for defining sets of rules. 

#### Basic usage

```php
use App\Rules\CustomRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;
use Sourcetoad\RuleHelper\RuleSet;

class CreateBlogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => RuleSet::create()
                ->required()
                ->unique('posts', 'title', fn(Unique $rule) => $rule->withoutTrashed())
                ->rule(new CustomRule)
                ->max(255),
            'body' => RuleSet::create()
                ->required(),
        ];
    }
}
```

### Rule

The `Rule` class provides the same methods as the Laravel `\Illuminate\Validation\Rule` class, with the rest of the
built-in rules exposed via static methods.

#### Basic usage

```php
use Illuminate\Foundation\Http\FormRequest;
use Sourcetoad\RuleHelper\Rule;

class CreateBlogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => [
                Rule::required(),
                Rule::unique('posts'),
                Rule::max(255),
            ],
            'body' => [
                Rule::required(),
            ],
        ];
    }
}
```

### Additional helpers

#### Defined rule sets

The `RuleSet` class contains methods to define and reuse rule sets across the project.

To define a rule set call `RuleSet::define` in your app service provider's boot method.

```php
    public function boot(): void
    {
        RuleSet::define('existing_email', RuleSet::create()->email()->exists('users'));
    }
```

The defined set can then be used in rules using `RuleSet::useDefined`.

```php
    public function rules(): array
    {
        return [
            'to' => RuleSet::useDefined('existing_email')->required(),
            'bcc' => RuleSet::useDefined('existing_email'),
        ];
    }
```

To concatenate a defined ruleset you can spread the ruleset as arguments for the concat method.

```php
RuleSet::create()->required()->concat(...RuleSet::useDefined('existing_email'));
```

#### requiredIfAll

Accepts multiple `RequiredIf` rules and only marks as required if all return true.

#### requiredIfAny

Accepts multiple `RequiredIf` rules and marks as required if any return true.
