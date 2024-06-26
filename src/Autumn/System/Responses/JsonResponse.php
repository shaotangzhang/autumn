<?php

namespace Autumn\System\Responses;

use Autumn\System\Response;

/**
 * JsonResponse class that extends the Response class.
 * This class is designed to handle JSON responses.
 */
class JsonResponse extends Response
{
    /**
     * Send the headers for the JSON response.
     */
    protected function sendHeaders(): void
    {
        // Set the Content-Type header to application/json
        $this->setHeader('Content-Type', 'application/json');

        // Call the parent sendHeaders method to handle any additional headers
        parent::sendHeaders();
    }

    /**
     * Send the JSON encoded content.
     */
    protected function sendContents(): void
    {
        // Echo the JSON encoded content
        echo json_encode($this->getContent());
    }
}