<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System\Responses;

use Autumn\System\Response;

class CallableResponse extends Response
{
    private mixed $callable;

    public function __construct(callable $callable,
                                int      $statusCode = null,
                                string   $reasonPhrase = null,
                                string   $protocolVersion = null)
    {
        parent::__construct(null,
            $statusCode ?? static::DEFAULT_STATUS_CODE,
            $reasonPhrase ?? static::DEFAULT_REASON_PHRASE,
            $protocolVersion ?? static::DEFAULT_VERSION
        );

        $this->callable = $callable;
    }

    /**
     * @return mixed
     */
    public function getCallable(): mixed
    {
        return $this->callable;
    }

    /**
     * @param callable $callable
     */
    public function setCallable(callable $callable): void
    {
        $this->callable = $callable;
    }

    protected function sendContents(): void
    {
        if (call_user_func($this->callable) !== false) {
            parent::sendContents();
        }
    }
}