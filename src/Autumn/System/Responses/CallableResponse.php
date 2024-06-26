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
    private ?array $context = null;

    public function __construct(callable $callable, int $statusCode = null, string|array $reasonPhrase = null, string $protocolVersion = null)
    {
        parent::__construct(null,
            $statusCode,
            $reasonPhrase,
            $protocolVersion
        );

        $this->callable = $callable;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): void
    {
        $this->context = $context;
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

    protected function sendContent(): void
    {
        if (call_user_func($this->callable) !== false) {
            parent::sendContent();
        }
    }
}