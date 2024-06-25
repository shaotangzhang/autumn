<?php

namespace Autumn\System\Responses;

use Autumn\System\Response;
use Autumn\System\Service;
use Autumn\System\Templates\TemplateService;
use Psr\Http\Message\ResponseInterface;

class ResponseService extends Service implements ResponseHandlerInterface
{
    /**
     * @var array<ResponseHandlerInterface>
     */
    private array $handlers = [
        TemplateService::class
    ];

    protected static function createDefaultInstance(): static
    {
        $instance = new static;

        $classes = [];
        foreach (glob(__DIR__ . '/Handlers/*ResponseHandler.php') as $file) {
            $classes[] = __NAMESPACE__ . '\\Handlers\\' . basename($file, '.php');
        }

        $instance->addHandler(...$classes);
        return $instance;
    }

    public function addHandler(string|ResponseHandlerInterface ...$handlers): static
    {
        foreach ($handlers as $handler) {
            if (is_subclass_of($handler, ResponseHandlerInterface::class)) {
                $this->handlers[] = $handler;
            }
        }
        return $this;
    }

    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        foreach ($this->handlers as $handler) {
            try {

                if (is_string($handler)) {
                    $handler = new $handler;
                }

                $response = $handler->respond($data, $statusCode, $context);
                if ($response instanceof ResponseInterface) {
                    return $response;
                }
            } catch (\Throwable $ex) {
                exit($ex);
            }
        }

        // Default response if no handler produced a valid response
        return new Response(
            $data,
            $statusCode ?? 200,
            $context['reasonPhrase'] ?? $context['message'] ?? null
        );
    }
}