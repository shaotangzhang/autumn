<?php
namespace Autumn\Http\Message;

trait HeadersTrait
{
    /**
     * @var array<string, array<string>> $headers
     */
    private array $headers = [];

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[self::formatHeaderName($name)]);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[self::formatHeaderName($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    protected static function formatHeaderValue(mixed $value): array
    {
        if (!is_array($value)) {

            if ($value instanceof \DateTimeInterface) {
                $value = gmdate('D, d M Y H:i:s', $value->getTimestamp()) . ' GMT';
            }

            return [(string)$value];
        }

        return array_map(__METHOD__, $value);
    }

    public function withHeader(string $name, mixed $value): static
    {
        $headerName = self::formatHeaderName($name);
        $value = self::formatHeaderValue($value);

        if (isset($this->headers[$headerName])) {
            $existing = $this->headers[$headerName];
            if (count($existing) === count($value)) {
                if (array_diff($existing, $value) === []) {
                    return $this;
                }
            }
        }

        $clone = clone $this;
        $clone->headers[$headerName] = $value;
        return $clone;
    }

    public function withAddedHeader(string $name, mixed $value): static
    {
        if (empty($value)) {
            return $this;
        }

        $headerName = self::formatHeaderName($name);
        $values = self::formatHeaderValue($value);

        $clone = clone $this;
        if (!isset($clone->headers[$headerName])) {
            $clone->headers[$headerName] = $values;
        } else {
            if (is_array($value)) {
                array_push($clone->headers[$headerName], ...$values);
            } else {
                $clone->headers[$headerName][] = $values;
            }
        }
        return $clone;
    }

    public function withoutHeader(string $name): static
    {
        $headerName = self::formatHeaderName($name);
        if (!isset($this->headers[$headerName])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->headers[$headerName]);
        return $this;
    }

    /**
     * Formats the header name according to HTTP header naming conventions.
     *
     * @param string $name
     * @return string
     */
    protected static function formatHeaderName(string $name): string
    {
        // Replaces non-alphanumeric characters with a space
        $temp = preg_replace('/[^a-z0-9]+/i', ' ', $name);
        // Uppercase the first character of each word after trimming whitespace
        $temp = ucwords(trim($temp));
        // Replace spaces with hyphens and converts the string to lowercase
        return str_replace(' ', '-', strtolower($temp));
    }

    protected function addHeader(string $name, string ...$values): static
    {
        $headerName = self::formatHeaderName($name);
        $this->headers[$headerName] ??= [];
        array_push($this->headers[$headerName], ...$values);
        return $this;
    }

    protected function setHeader(string $name, string ...$values): void
    {
        $this->headers[self::formatHeaderName($name)] = $values;
    }

    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $values) {
            $headerName = self::formatHeaderName($name);

            if (empty($values)) {
                unset($this->headers[$headerName]);
            } else {
                if (!is_array($values)) {
                    $values = [$values];
                }

                $this->headers[$headerName] = $values;
            }
        }
    }

    protected function withHeaders(array $headers): static
    {
        $clone = clone $this;
        $clone->setHeaders($headers);
        return $clone;
    }
}