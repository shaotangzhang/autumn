<?php

namespace Autumn\System\Responses;

use Autumn\System\Response;

/**
 * RedirectResponse class that extends the Response class.
 * This class is designed to handle HTTP redirect responses.
 */
class RedirectResponse extends Response
{
    public const DEFAULT_STATUS_CODE = 302;
    public const DEFAULT_REASON_PHRASE = 'Moved';

    /**
     * Constructor to initialize the RedirectResponse object.
     *
     * @param string $location The URL to redirect to.
     * @param int|null $statusCode The HTTP status code.
     * @param string|null $reasonPhrase The reason phrase.
     * @param string|null $protocolVersion The HTTP protocol version.
     */
    public function __construct(
        private string $location,
        int            $statusCode = null,
        string   $reasonPhrase = null,
        string         $protocolVersion = null
    )
    {
        parent::__construct(
            null,
            $statusCode ?? self::DEFAULT_STATUS_CODE,
            $reasonPhrase ?? self::DEFAULT_REASON_PHRASE,
            $protocolVersion
        );
    }

    /**
     * Get the URL to redirect to.
     *
     * @return string The redirect location.
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set the URL to redirect to.
     *
     * @param string $location The redirect location.
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * Send the headers for the redirect response.
     */
    protected function sendHeaders(): void
    {
        // Set the Location header to the redirect URL
        $this->setHeader('Location', $this->location);

        // Call the parent sendHeaders method to handle any additional headers
        parent::sendHeaders();
    }
}