<?php

namespace Autumn\System\Responses;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Response;
use Autumn\System\Responses\Handlers\HTMLDocumentResponseHandler;
use Autumn\System\Responses\Handlers\JsonResponseHandler;
use Autumn\System\Responses\Handlers\ThrowableResponseHandler;
use Autumn\System\Responses\Handlers\ViewResponseHandler;
use Autumn\System\Responses\Handlers\XMLDocumentResponseHandler;
use Autumn\System\Service;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * ResponseService class that handles various types of responses.
 * This service manages response handlers based on content types and produces
 * the appropriate HTTP response.
 */
class ResponseService implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public const DEFAULT_HANDLERS = [
        ViewResponseHandler::class,
        ThrowableResponseHandler::class,
        HTMLDocumentResponseHandler::class,
    ];

    private static array $contentTypeHandlers = [
        '*/*' => self::DEFAULT_HANDLERS,
        'text/html' => self::DEFAULT_HANDLERS,
        'text/xml' => [XMLDocumentResponseHandler::class, ThrowableResponseHandler::class],
        'application/json' => [JsonResponseHandler::class, ThrowableResponseHandler::class],
    ];

    private static array $registeredHandlers = [];

    /**
     * Registers a handler for a specific content type.
     *
     * @param string $contentType The MIME type.
     * @param string|ResponseHandlerInterface ...$handlers The handlers to register.
     */
    public static function registerContentTypeHandler(string $contentType, string|ResponseHandlerInterface ...$handlers): void
    {
        $mime = strtolower($contentType);
        self::$contentTypeHandlers[$mime] ??= [];
        array_push(self::$contentTypeHandlers[$mime], ...$handlers);
    }

    /**
     * Registers a general handler for all content types.
     *
     * @param string|ResponseHandlerInterface ...$handlers The handlers to register.
     */
    public static function registerHandler(string|ResponseHandlerInterface ...$handlers): void
    {
        array_push(self::$registeredHandlers, ...$handlers);
    }

    /**
     * Generates a response based on the provided data and content type.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|string|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response.
     */
    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        $handlers = self::$registeredHandlers;

        // Parse the Accept header to determine the appropriate handlers
        $accepts = explode(',', $_SERVER['HTTP_ACCEPT'] ?? '*/*');
        foreach ($accepts as $accept) {
            $accept = strtolower(trim(explode(',',$accept)[0]));
            foreach (self::$contentTypeHandlers[$accept] ?? [] as $handler) {
                $handlers[] = $handler;
            }
        }

        // Process each handler and return the response if valid
        foreach (array_unique($handlers) as $handler) {
            if (is_subclass_of($handler, ResponseHandlerInterface::class)) {
                if (is_string($handler)) {
                    $handler = make($handler, true);
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