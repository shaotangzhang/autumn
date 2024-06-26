<?php

namespace Autumn\System\Responses\Handlers;

use Autumn\Exceptions\RedirectException;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\Responses\RedirectResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\System\View;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * ThrowableResponseHandler class that handles responses for Throwable objects.
 * This class implements the ContextInterface and ResponseHandlerInterface.
 */
class ThrowableResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    /**
     * Respond with appropriate response for Throwable objects.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof \Throwable) {

            // Handle JSON response for Throwable
            switch ($_SERVER['HTTP_ACCEPT'] ?? null) {
                case 'application/json':
                    return JsonResponseHandler::context()->respond($data, $statusCode, $context);
                case 'text/xml':
                    return XMLDocumentResponseHandler::context()->respond($data, $statusCode, $context);
                // add more ...
            }

            // Handle RedirectException
            if ($data instanceof RedirectException) {
                return new RedirectResponse($data->getLocation(), $data->getCode(), $data->getMessage());
            }

            // Handle other error responses using a view
            $view = new View('/error/index', ['error' => $data]);
            $context['exception_handler'] = function () use ($data) {
                if (env('DEBUG')) {
                    echo $data;
                } else {
                    echo $data->getMessage() ?: ('Server exception: ' . $data::class);
                }
            };

            return ViewResponseHandler::context()->respond($view, $statusCode, $context);
        }

        // Return null if the data is not handled by this handler
        return null;
    }
}