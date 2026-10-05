# Nested page

- An indented fence inside a list:

  ```php title="module.php"
  namespace Marko\Declared\Space;

  use function Marko\Env\env;
  use Marko\Routing\Attributes\{Get, Post as PostRoute};
  use Marko\Fictional\Example\Service;

  $response = new \Marko\Routing\Http\Response();
  $missing = \Marko\Missing\Fqcn::class;
  ```
