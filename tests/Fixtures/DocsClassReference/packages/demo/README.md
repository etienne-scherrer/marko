# Fixture README

A fenced PHP block with one real class and one bogus class:

```php
use Marko\Routing\Http\Response;
use Marko\Nope\Thing;
```

Prose mentions of `Marko\Prose\Only` are ignored, and so are non-PHP blocks:

```bash
echo 'Marko\Bash\Only'
```

```json
{ "autoload": { "psr-4": { "Marko\\Json\\Only\\": "src/" } } }
```
