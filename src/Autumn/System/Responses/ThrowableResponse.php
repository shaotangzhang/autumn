<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System\Responses;

use Autumn\System\Response;

class ThrowableResponse extends Response
{
    public const DEFAULT_STATUS_CODE = 500;
    public const DEFAULT_REASON_PHRASE = 'Service is unavailable.';

    public function __construct(private \Throwable $exception,
                                int                $statusCode = null,
                                string             $reasonPhrase = null,
                                string             $protocolVersion = null)
    {
        parent::__construct(null,
            ($statusCode ?? $this->exception->getCode()) ?: static::DEFAULT_STATUS_CODE,
            ($reasonPhrase ?? $this->exception->getMessage()) ?: static::DEFAULT_REASON_PHRASE,
            $protocolVersion
        );
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @param \Throwable $exception
     */
    public function setException(\Throwable $exception): void
    {
        $this->exception = $exception;
    }
}