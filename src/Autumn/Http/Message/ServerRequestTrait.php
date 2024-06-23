<?php
namespace Autumn\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

trait ServerRequestTrait
{
    use RequestTrait;

    private array $serverParams = [];
    private array $cookieParams = [];
    private array $queryParams = [];
    private array $uploadedFiles = [];

    private array|object|null $parsedBody = null;

    public static function fromServerRequest(ServerRequestInterface $request, array $attributes = null): static
    {
        $instance = self::fromRequestInterface($request);

        $instance->serverParams = $request->getServerParams();
        $instance->cookieParams = $request->getCookieParams();
        $instance->queryParams = $request->getQueryParams();
        $instance->parsedBody = $request->getParsedBody();
        $instance->uploadedFiles = $request->getUploadedFiles();
        $instance->attributes = $attributes ?? $request->getAttributes();

        return $instance;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): static
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): static
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * @return array<UploadedFileInterface>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    public function getParsedBody(): object|array|null
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): static
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    private array $attributes = [];

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, mixed $value): static
    {
        if (($this->attributes[$name] ?? null) === $value) {
            return $this;
        }

        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute(string $name): static
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }

    private function setAttribute(string $name, mixed $value): void
    {
        if ($value === null) {
            unset($this->attributes[$name]);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    private function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    private function removeAllAttributes(): void
    {
        $this->attributes = [];
    }

    private function withAttributes(array $attributes): static
    {
        $clone = clone $this;
        $clone->attributes = $attributes;
        return $clone;
    }

    private function withAddedAttributes(array $attributes): static
    {
        $clone = clone $this;
        $clone->attributes = array_merge($clone->attributes, $attributes);
        return $clone;
    }

    public function getAttributeNames(): array
    {
        return array_keys($this->attributes);
    }
}