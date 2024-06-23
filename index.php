<?php

use Autumn\System\Request;
use Autumn\System\Response;
use Autumn\System\Responses\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class App
{
    public static function boot(): ResponseInterface
    {
        $app = new Application;
        return $app->process(Request::capture());
    }
}

class Application implements RequestHandlerInterface
{

    private MiddlewareInterface $middleware;

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = Route::matches($request)) {
            return $route->handle($request);
        }

        exit('Error 404');
    }
}

class Route implements RequestHandlerInterface
{

    public static function matches(ServerRequestInterface $request): ?static
    {
        return new static;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->getController();
        $command = $this->getCommand();

        $result = call([$controller, $command], $request);

        $service = new ResponseService;
        return $service->respond($result);
    }
}

class ResponseService implements ResponseHandlerInterface
{
    /**
     * @var array<string|ResponseHandlerInterface>
     */
    private array $handlers = [
        ViewResponseHandler::class
    ];

    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        foreach ($this->handlers as $handlerClass) {
            if ($handler = app($handlerClass)) {
                $data = $handler->respond($data, $statusCode, $context);
                if ($data instanceof ResponseInterface) {
                    return $data;
                }
                if ($data === null) {
                    return null;
                }
            }
        }

        $message = is_string($context) ? $context : $context['reasonPhrase'] ?? $context['message'] ?? '';
        return new Response((string)$data, $statusCode, $message);
    }
}

class ViewResponseHandler implements ResponseHandlerInterface
{
    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        if ($data instanceof View) {
            fire('view.before.render', $data, [], $context);
        }

        return $data;
    }
}

class View
{
    public function slot(string $name, callable $default = null, array|ArrayAccess $args = null, array $context = null): void
    {
        fire('view.before.slot.' . $name, $this, $args, $context);
        foreach ($this->slots[$name] ?? ($default ? [$default] : []) as $callback) {
            call($callback, $args, $context);
        }
        fire('view.after.slot.' . $name, $this, $args, $context);
    }
}

?>

<!doctype html>
<html lang="<?= attr($this->lang ?: env('SITE_LANG', 'en')) ?>">
<head>
    <?php $this->slot('head', function () { ?>
        <title><?= html('page.title', $this->title) ?></title>

        <?php $this->slot('styles', function () { ?>
            <link href="/assets/css/common.css" rel="stylesheet">
        <?php }); ?>
    <?php }); ?>
</head>
<body>

<?php $this->slot('header', function () { ?>
    <header>Logo | <a href="#">About</a></header>
<?php }); ?>

<?php $this->slot('main', function () { ?>
    <main><?php $this->contents(); ?></main>
<?php }); ?>

<?php $this->slot('footer', function () { ?>
    <header>Copyright reserved</header>
<?php }); ?>

<?php $this->slot('scripts', function () { ?>
    <script src="/assets/js/common.js"></script>
<?php }); ?>

</body>
</html>
