# marko/validation

Input validation with built-in rules, string-based rule parsing, and custom rule support — validate data before it reaches your domain.

## Installation

```bash
composer require marko/validation
```

## Quick Example

```php
use Marko\Validation\Contracts\ValidatorInterface;

$errors = $this->validator->validate([...$request->input(), ...$request->files()], [
    'name' => 'required|string|max:100',
    'email' => 'required|email',
    'age' => 'nullable|integer|min:18',
    'avatar' => 'nullable|file|image|max_size:2048',
]);

if ($errors->isNotEmpty()) {
    // handle errors
}
```

## Documentation

Full usage, API reference, and examples: [marko/validation](https://marko.build/docs/packages/validation/)
