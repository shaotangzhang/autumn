<?php

namespace Autumn\System\Responses\Handlers;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Controller;
use Autumn\System\Responses\CallableResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\System\Templates\TemplateService;
use Autumn\System\View;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * ViewResponseHandler class that handles responses with View objects.
 * This class implements the ContextInterface and ResponseHandlerInterface.
 */
class ViewResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    /**
     * Respond with a View object.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof View) {
            // Attempt to use a TemplateService to render the view
            if ($service = make(TemplateService::class, true)) {
                $context['use_layout'] = !!env('THEME_USE_LAYOUT');
                return new CallableResponse(fn() => $service->outputView($data, $context), $statusCode);
            }

            // If TemplateService is not available, handle with JSON response handler
            $controller = $context[Controller::class] ?? null;
            if ($controller instanceof Controller) {
                $data = $data->toArray() + $controller->toArray();
            }

            return JsonResponseHandler::context()->respond($data, $statusCode, $context);
        }

        // Return null if the data is not handled by this handler
        return null;
    }
}