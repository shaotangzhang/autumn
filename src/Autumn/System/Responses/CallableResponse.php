<?php
namespace Autumn\System\Responses;

use Autumn\System\Response;

/**
 * CallableResponse class that extends the Response class.
 * This class is designed to handle responses that execute a callable.
 */
class CallableResponse extends Response
{
    private mixed $callable;
    private ?array $context = null;

    /**
     * Constructor to initialize the CallableResponse object.
     *
     * @param callable $callable The callable to be executed.
     * @param int|null $statusCode The HTTP status code.
     * @param string|null $reasonPhrase The reason phrase.
     * @param string|null $protocolVersion The HTTP protocol version.
     */
    public function __construct(
        callable $callable,
        int $statusCode = null,
        string $reasonPhrase = null,
        string $protocolVersion = null
    ) {
        parent::__construct(null, $statusCode, $reasonPhrase, $protocolVersion);
        $this->callable = $callable;
    }

    /**
     * Get the context of the response.
     *
     * @return array|null The context array or null if not set.
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Set the context of the response.
     *
     * @param array|null $context The context array.
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    /**
     * Get the callable associated with the response.
     *
     * @return mixed The callable.
     */
    public function getCallable(): mixed
    {
        return $this->callable;
    }

    /**
     * Set the callable for the response.
     *
     * @param callable $callable The callable to set.
     */
    public function setCallable(callable $callable): void
    {
        $this->callable = $callable;
    }

    /**
     * Send the content of the response by executing the callable.
     */
    protected function sendContent(): void
    {
        // Execute the callable and check the result
        if (call_user_func($this->callable) !== false) {
            // Call the parent sendContent method to handle any additional content sending
            parent::sendContent();
        }
    }
}
