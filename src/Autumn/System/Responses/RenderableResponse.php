<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System\Responses;

use Autumn\Interfaces\Renderable;
use Autumn\System\Response;

class RenderableResponse extends Response
{
    public function __construct(private Renderable $renderable, int $statusCode = null, string $reasonPhrase = null, string $protocolVersion = null)
    {
        parent::__construct(null, $statusCode, $reasonPhrase, $protocolVersion);
    }

    /**
     * @return Renderable
     */
    public function getRenderable(): Renderable
    {
        return $this->renderable;
    }

    /**
     * @param Renderable $renderable
     */
    public function setRenderable(Renderable $renderable): void
    {
        $this->renderable = $renderable;
    }

    protected function sendContents(): void
    {
        $this->renderable->render();
        parent::sendContent();
    }
}