<?php

namespace Autumn\System\Responses;

use Autumn\System\Response;
use Autumn\System\Responses\Handlers\HTMLDocumentResponseHandler;
use Autumn\System\Responses\Handlers\JsonResponseHandler;
use Autumn\System\Responses\Handlers\ThrowableResponseHandler;
use Autumn\System\Responses\Handlers\ViewResponseHandler;
use Autumn\System\Responses\Handlers\XMLDocumentResponseHandler;
use Autumn\System\Service;
use Psr\Http\Message\ResponseInterface;

class ResponseService extends Service implements ResponseHandlerInterface
{
    public const DEFAULT_HANDLERS = [
        ViewResponseHandler::class,
        ThrowableResponseHandler::class,
        HTMLDocumentResponseHandler::class,
    ];

    private static array $mimeTypeHandlers = [
        '*/*' => self::DEFAULT_HANDLERS,
        'text/html' => self::DEFAULT_HANDLERS,
        'text/xml' => [XMLDocumentResponseHandler::class],
        'application/json' => [JsonResponseHandler::class],
    ];

    private static array $registeredHandlers = [];

    public static function registerMimeTypeHandler(string $mimeType, string|ResponseHandlerInterface ...$handlers): void
    {
        $mime = strtolower($mimeType);
        self::$mimeTypeHandlers[$mime] ??= [];
        array_push(self::$mimeTypeHandlers[$mime], ...$handlers);
    }

    public static function registerHandler(string|ResponseHandlerInterface ...$handlers): void
    {
        array_push(self::$registeredHandlers, ...$handlers);
    }

    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        $handlers = self::$registeredHandlers;

        $accepts = explode(',', $_SERVER['HTTP_ACCEPT'] ?? '*/*');
        foreach ($accepts as $accept) {
            $accept = strtolower(trim($accept));
            foreach (self::$mimeTypeHandlers[$accept] ?? [] as $handler) {
                $handlers[] = $handler;
            }
        }

        foreach (array_unique($handlers) as $handler) {
            if (is_subclass_of($handler, ResponseHandlerInterface::class)) {
                if (is_string($handler)) {
                    $handler = app($handler, true);
                }

                if ($handler instanceof ResponseHandlerInterface) {
                    $response = $handler->respond($data, $statusCode, $context);
                    if ($response instanceof ResponseInterface) {
                        return $response;
                    }
                }
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