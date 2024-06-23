<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/02/2024
 */

namespace Autumn\Http;

use Autumn\Http\Message\ResponseTrait;
use Autumn\Http\Message\Stream;
use Autumn\Http\Message\UploadedFile;
use Autumn\Lang\MimeType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class HttpResponse implements ResponseInterface
{
    use ResponseTrait;

    public const RESPONSE_OPTION_DETECT_MIMETYPE = 'RESPONSE_OPTION_DETECT_MIMETYPE';
    public const RESPONSE_OPTION_AUTO_DIGEST = 'RESPONSE_OPTION_AUTO_DIGEST';
    public const DIGEST_MD5 = 'md5';

    private ?string $digest = null;
    private ?string $mimeType = null;
    private ?array $mimeInfo = null;

    public function __construct(int $statusCode, string $reasonPhrase, array $headers, ?StreamInterface $body)
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @throws HttpException
     */
    public static function fromCURLHandler(\CurlHandle $curl, array $context = null): static
    {
        // 获取请求头的字符串
        $headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT);

        if (empty($headerSent)) {
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }

        $response = curl_exec($curl);
        if ($response === false) {
            throw new HttpException('Invalid response.');
        }

        list($headers, $body) = explode("\r\n\r\n", $response . "\r\n\r\n", 2);

        // 解析 HTTP 头
        $headerLines = explode("\r\n", $headers);
        $statusLine = array_shift($headerLines);
        list(, $statusCode, $statusText) = explode(' ', $statusLine, 3);

        $parsedHeaders = [];

        $bodyStream = Stream::fromString($body);

        $instance = new static((int)$statusCode, $statusText, $parsedHeaders, $bodyStream);
        foreach ($headerLines as $headerLine) {
            list($name, $value) = explode(': ', $headerLine, 2);
            $instance->setHeader($name, $value);
        }

        if ($context[static::RESPONSE_OPTION_DETECT_MIMETYPE] ?? null) {
            $instance->mimeType = MimeType::detectFromString($body, $info);
            if ($info['source'] = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL)) {
                $info['name'] = basename(preg_split('/[?#]+/', $info['source'])[0] ?? '');
            }
            $instance->mimeInfo = $info;

            if (!$instance->getHeader('Content-Type')) {
                $instance->setHeader('Content-Type', $instance->mimeType);
            }
        }

        switch ($digest = $context[static::RESPONSE_OPTION_AUTO_DIGEST] ?? null) {
            case 'md5':
            case 'MD5':
            case static::DIGEST_MD5:
                $instance->digest = md5($body);
                break;
        }

        return $instance;
    }

    /**
     * Parse the response body as SimpleXMLElement (for XML).
     *
     * @return \SimpleXMLElement The SimpleXMLElement object.
     * @throws \RuntimeException If unable to parse the body as SimpleXMLElement.
     */
    public function xml(): \SimpleXMLElement
    {
        $xml = simplexml_load_string($this->text());

        if ($xml === false) {
            throw new \RuntimeException('Unable to parse body as SimpleXMLElement.');
        }

        return $xml;
    }

    /**
     * Parse the response body as DOMDocument (for generic HTML/XML).
     *
     * @return \DOMDocument The DOMDocument object.
     * @throws \RuntimeException If unable to parse the body as DOMDocument.
     */
    public function document(): \DOMDocument
    {
        $bodyContents = $this->text();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $success = $dom->loadHTML($bodyContents, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_use_internal_errors(false);

        if (!$success) {
            throw new \RuntimeException('Unable to parse body as DOMDocument.');
        }

        return $dom;
    }

    /**
     * Parse the response body as HTMLDocument.
     *
     * @return HTMLDocument The HTMLDocument object.
     * @throws \RuntimeException If unable to parse the body as HTMLDocument.
     */
    public function html(): HTMLDocument
    {
        return HTMLDocument::fromResponse($this);
    }

    /**
     * Parse the response body as plain text.
     *
     * @return string The plain text.
     */
    public function text(): string
    {
        $this->getBody()->rewind();
        return $this->getBody()->getContents();
    }

    /**
     * Parse the response body as JSON.
     *
     * @return mixed The decoded JSON data.
     * @throws \JsonException If the JSON data cannot be decoded.
     */
    public function json(bool $returnObject = null): mixed
    {
        return json_decode($this->text(), !$returnObject, 512, JSON_THROW_ON_ERROR);
    }

    public function saveAsUploadedFile(): UploadedFileInterface
    {
        return UploadedFile::fromStream($this->body, [
            'type' => $this->mimeType,
            'info' => $this->mimeInfo,
            'name' => $this->mimeInfo['name'] ?? null,
            'digest' => $this->digest,
        ]);
    }
}