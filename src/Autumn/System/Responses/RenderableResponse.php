<?php

namespace Autumn\System\Responses;

use Autumn\Interfaces\Renderable;
use Autumn\System\Response;

/**
 * RenderableResponse class that extends the Response class.
 * This class is designed to handle responses that implement the Renderable interface.
 */
class RenderableResponse extends Response
{
    /**
     * Constructor to initialize the RenderableResponse object.
     *
     * @param Renderable $renderable The renderable content.
     * @param int|null $statusCode The HTTP status code.
     * @param string|null $reasonPhrase The reason phrase.
     * @param string|null $protocolVersion The HTTP protocol version.
     */
    public function __construct(
        private Renderable $renderable,
        int                $statusCode = null,
        string             $reasonPhrase = null,
        string             $protocolVersion = null
    )
    {
        parent::__construct(null, $statusCode, $reasonPhrase, $protocolVersion);
    }

    /**
     * Get the renderable content.
     *
     * @return Renderable The renderable content.
     */
    public function getRenderable(): Renderable
    {
        return $this->renderable;
    }

    /**
     * Set the renderable content.
     *
     * @param Renderable $renderable The renderable content.
     */
    public function setRenderable(Renderable $renderable): void
    {
        $this->renderable = $renderable;
    }

    /**
     * Send the renderable content as part of the response.
     */
    protected function sendContents(): void
    {
        // Render the content using the renderer's render method
        $this->renderable->render();

        // Call the parent sendContent method to handle any additional content sending
        parent::sendContent();
    }
}