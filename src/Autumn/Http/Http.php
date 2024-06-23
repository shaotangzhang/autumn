<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2/03/2024
 */

namespace Autumn\Http;

use Autumn\Exceptions\SystemException;
use Psr\Http\Message\UploadedFileInterface;

class Http
{
    /**
     * @throws HttpException
     */
    public static function request(string $method, string $url, mixed $postedData = null, array $headers = null, array $context = null): HttpResponse
    {
        $client = new Client($context['options'] ?? $context);
        $client->open($method, $url, $headers);
        if ($postedData !== null) {
            $client->setBody($postedData);
        }
        return $client->send();
    }

    /**
     * @throws HttpException
     */
    public static function get(string $url, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->get($url, $headers);
    }

    /**
     * @throws HttpException
     */
    public static function head(string $url, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->head($url, $headers);
    }

    /**
     * @throws HttpException
     */
    public static function delete(string $url, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->delete($url, $headers);
    }

    /**
     * @throws HttpException
     */
    public static function post(string $url, mixed $postedData = null, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->post($url, $postedData, $headers);
    }

    /**
     * @throws HttpException
     */
    public static function put(string $url, mixed $postedData = null, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->put($url, $postedData, $headers);
    }

    /**
     * @throws HttpException
     */
    public static function patch(string $url, mixed $postedData = null, array $headers = null, array $context = null): HttpResponse
    {
        return (new Client($context['options'] ?? $context))->patch($url, $postedData, $headers);
    }

    public static function download(string $url, mixed $postedData = null, array $context = null, HttpResponse &$response = null): ?UploadedFileInterface
    {
        $method = $context['method'] ?? 'GET';
        if (!is_string($method)) {
            throw SystemException::of('Invalid HTTP method.');
        }

        if ($headers = $context['headers'] ?? null) {
            if (!is_array($headers)) {
                throw SystemException::of('Invalid HTTP headers.');
            }
        }

        $options = $context['options'] ?? $context;
        $options[HttpResponse::RESPONSE_OPTION_DETECT_MIMETYPE] ??= true;
        $client = new Client($options);

        $request = $client->open(strtoupper($method), $url, $headers);
        if ($postedData !== null) {
            $request->setBody($postedData);
        }

        $response = $request->send();
        return $response->saveAsUploadedFile();
    }
}