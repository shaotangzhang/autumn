<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System\Responses;

use Autumn\System\Response;

class JsonResponse extends Response
{
    protected function sendHeaders(): void
    {
        $this->setHeader('Content-Type', 'application/json');
        parent::sendHeaders();
    }

    protected function sendContents(): void
    {
        echo json_encode($this->getContent());
    }
}