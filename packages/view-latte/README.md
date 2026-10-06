# marko/view-latte

Latte templating driver for the Marko Framework.

## Installation

```bash
composer require marko/view-latte
```

## Quick Example

```php
$view->render('blog::post/index', ['posts' => $posts]);
```

```latte
<a href={route('blog.post.show', [slug: $post->slug])}>{$post->title}</a>
```

## Documentation

Full usage, API reference, and examples: [marko/view-latte](https://marko.build/docs/packages/view-latte/)
