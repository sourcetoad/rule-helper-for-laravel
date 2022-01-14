# Rule Helper for Laravel

Adds helpers to make building Laravel rule arrays easier by providing helper methods for the built-in rules.

## Installation

```shell
$ composer config repositories.sourcetoad/rule-helper-for-laravel.git vcs https://github.com/sourcetoad/rule-helper-for-laravel.git
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
use Sourcetoad\RuleHelper\Support\Facades\RuleSet;

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

#### Extending

If extending `RuleSet` to add more helpers, to maintain IDE support you will need to switch away from the facade when
creating new sets.

```php
use App\Rules\CustomRule;

class RuleSet extends \Sourcetoad\RuleHelper\RuleSet
{
    public function customRule(): self
    {
        return $this->rule(new CustomRule);
    }
}
```

```diff
-use App\Rules\CustomRule;
+use App\Rules\RuleSet;
 use Illuminate\Foundation\Http\FormRequest;
 use Illuminate\Validation\Rules\Unique;
-use Sourcetoad\RuleHelper\Support\Facades\RuleSet;

 class CreateBlogRequest extends FormRequest
 {
     public function rules(): array
     {
         return [
             'title' => RuleSet::create()
                 ->required()
                 ->unique('posts', 'title', fn(Unique $rule) => $rule->withoutTrashed())
-                ->rule(new CustomRule)
+                ->customRule()
                 ->max(255),
             'body' => RuleSet::create()
                 ->required(),
         ];
     }
 }
```

### Rule

The `Rule` class is an extension of the Laravel `\Illuminate\Validation\Rule` class to expose the rest of the built-in
rules via static methods.

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

## Additional helpers

### `requiredIfAll`

Accepts multiple `RequiredIf` rules and only marks as required if all return true.

### `requiredIfAny`

Accepts multiple `RequiredIf` rules and marks as required if any return true.
