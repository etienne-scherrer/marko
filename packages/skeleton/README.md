# Marko Skeleton

Application skeleton for the [Marko Framework](https://marko.build).

## Installation

```bash
composer create-project marko/skeleton my-app
cd my-app
```

## What's Included

- `public/index.php` — Web entry point
- `app/` — Your application modules
- `modules/` — Third-party modules
- `config/` — Root configuration
- `storage/` — Logs, cache, sessions
- `.env.example` — Environment template

## Getting Started

1. Copy `.env.example` to `.env`
2. Install dev tools: `composer install`
3. Start the dev server: `marko up`
4. Visit http://localhost:8000

The skeleton ships a working Pest test harness out of the box. `tests/Pest.php` binds `Marko\Testing\TestCase` as the base, which registers PSR-4 autoloaders for `app/*` and `modules/*` automatically. Run tests with:

```bash
composer test
```

## Next Steps

Create a module inside `app/` (for example `app/foo/`, with a `composer.json` that maps `App\Foo\` to `src/`), then add a controller. Routes are declared with attributes:

```php
<?php

declare(strict_types=1);

namespace App\Foo\Controller;

use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;

class HomeController
{
    #[Get('/')]
    public function index(): Response
    {
        return new Response(
            body: 'Hello, Marko!',
        );
    }
}
```

See [Your First Application](https://marko.build/docs/getting-started/first-application/) for the full walkthrough, including the module's `composer.json`.

## Documentation

- [Your First Application](https://marko.build/docs/getting-started/first-application/)
- [Project Structure](https://marko.build/docs/getting-started/project-structure/)
- [Full Documentation](https://marko.build/docs/)
