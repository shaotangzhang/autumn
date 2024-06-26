<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Controller;
use Autumn\System\Responses\CallableResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\System\Templates\TemplateService;
use Autumn\System\View;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

class ViewResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof View) {
            if ($service = app(TemplateService::class, true)) {
                $context['use_layout'] = !!env('THEME_USE_LAYOUT');
                return new CallableResponse(
                    fn() => $service->outputView($data, $context),
                    $statusCode,
                    $context
                );
            }

            $controller = $context[Controller::class] ?? null;
            if ($controller instanceof Controller) {
                $data = $data->toArray() + $controller->toArray();
            }

            return JsonResponseHandler::context()->respond($data, $statusCode, $context);
        }

        return null;
    }
}