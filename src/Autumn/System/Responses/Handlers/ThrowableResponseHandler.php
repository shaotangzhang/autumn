<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Exceptions\RedirectException;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\Responses\RedirectResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\System\View;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

class ThrowableResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        if ($data instanceof \Throwable) {

            // Handle Throwable response
            if (($_SERVER['HTTP_ACCEPT'] ?? null) === 'application/json') {
                // Handle JSON response for Throwable
                return JsonResponseHandler::context()->respond($data, $statusCode, $context);
            }

            // Handle other response types
            if ($data instanceof RedirectException) {
                // Handle RedirectException
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

        return null;
    }
}
