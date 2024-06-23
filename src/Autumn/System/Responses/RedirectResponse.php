<?php

namespace Autumn\System\Responses;

use Autumn\System\Response;

class RedirectResponse extends Response
{
    public const DEFAULT_STATUS_CODE = 302;

    public const DEFAULT_REASON_PHRASE = 'Moved';

    public function __construct(private string $location, int $statusCode = null, string $reasonPhrase = null, string $protocolVersion = null)
    {
        parent::__construct(null, $statusCode, $reasonPhrase, $protocolVersion);
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    protected function sendHeaders(): void
    {
        $this->setHeader('Location', $this->location);
        parent::sendHeaders();
    }
}