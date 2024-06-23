<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/02/2024
 */

namespace Autumn\Http;

use Autumn\Exceptions\ValidationException;
use Autumn\Http\Message\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Client
{
    private array $options;
    private array $cookies = [];
    private CookieJar $cookieJar;

    private ?RequestInterface $request = null;
    private mixed $curlHandle = null;
    private ?\CurlMultiHandle $multiHandle = null;

    public function __construct(array $options = null)
    {
        $this->options = $options ? array_merge(static::defaultOptions(), $options) : static::defaultOptions();
        $this->cookieJar = new CookieJar;
    }

    public static function fromResource(mixed $resource): static
    {
        if ($resource instanceof \CurlHandle) {
            $instance = new static;
            $instance->curlHandle = $resource;
            return $instance;
        }

        if (is_resource($resource)) {
            if (get_resource_type($resource) === 'curl') {
                $instance = new static;
                $instance->curlHandle = $resource;
                return $instance;
            }
        }

        throw ValidationException::of('Invalid CUrl resource.');
    }

    public static function defaultOptions(): array
    {
        return [
            // Add default options here
            'follow_location' => true,
            'return_transfer' => true,
            'max_redirects' => 5,
            'timeout' => 10,

            'ssl_verify_peer' => false,
            'ssl_verify_host' => false,

            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.6167.189 Safari/537.36',
        ];
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function getBody(): ?StreamInterface
    {
        return $this->request?->getBody();
    }

    public function setBody(string|\Stringable|StreamInterface|array $body): void
    {
        if (is_array($body)) {
            $body = http_build_query($body);
        }

        if (!$body instanceof StreamInterface) {
            $body = Stream::fromString((string)$body);
        }

        $this->request = $this->request->withBody($body);
    }

    public function open(string $method, string $path, array $headers = null): static
    {
        $this->request = new HttpRequest($method, $path, $headers);
        $cookieHeader = $this->generateCookieHeader();
        if ($cookieHeader !== '') {
            $headers['Cookie'] = $cookieHeader;
        }
        return $this;
    }

    /**
     * @throws HttpException
     */
    public function send(): HttpResponse
    {
        $this->initializeCurl();
        $this->setCurlOptions();

        $response = curl_exec($this->curlHandle);
        if ($response === false) {
            throw new \RuntimeException('cURL error: ' . curl_error($this->curlHandle));
        }

        $response = HttpResponse::fromCURLHandler($this->curlHandle, $this->options);
        $this->processSetCookieHeader($response);
        $this->closeCurl();
        return $response;
    }

    /**
     * @param \CurlMultiHandle $multiHandle
     *
     * @deprecated
     */
    public function asyncLoop(\CurlMultiHandle $multiHandle): void
    {
        if ($this->multiHandle) {

        }

        $active = null;
        do {
            $mrc = curl_multi_exec($multiHandle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multiHandle) == -1) {
                usleep(100);
            }

            do {
                $mrc = curl_multi_exec($multiHandle, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    public function enqueue(\CurlHandle $handle, callable $callback = null): int
    {
        if (!$this->multiHandle) {
            $this->multiHandle = curl_multi_init();
        }

        $result = curl_multi_add_handle($this->multiHandle ??= curl_multi_init(), $handle);

        return $result;
    }

    /**
     * @param callable|null $resolve
     * @param callable|null $reject
     * @param callable|null $complete
     * @return int
     * @throws HttpException
     *
     * @deprecated
     */
    public function async(callable $resolve = null, callable $reject = null, callable $complete = null): int
    {
        // $this->multiHandle ??= curl_multi_init();

        // $this->initializeCurl();
        // $this->setCurlOptions();

        // $result = curl_multi_add_handle($this->multiHandle ??= curl_multi_init(), $this->curlHandle);

        // $response = curl_exec($this->curlHandle);
        // if ($response === false) {
        //     throw new \RuntimeException('cURL error: ' . curl_error($this->curlHandle));
        // }

        // $response = HttpResponse::fromCURLHandler($this->curlHandle);
        // $this->processSetCookieHeader($response);
        // $this->closeCurl();

        // $active = null;
        // do {
        //     $mrc = curl_multi_exec($this->multiHandle, $active);
        // } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        // while ($active && $mrc == CURLM_OK) {
        //     if (curl_multi_select($this->multiHandle) == -1) {
        //         usleep(100);
        //     }

        //     do {
        //         $mrc = curl_multi_exec($this->multiHandle, $active);
        //     } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        // }

        // foreach (['resolve', 'reject', 'complete'] as $type) {
        //     $response = curl_multi_getcontent($handles[$type]);
        //     if ($response === false) {
        //         $reject($type, 'cURL error: ' . curl_error($handles[$type]));
        //     } else {
        //         $resolve($type, $response);
        //     }

        //     curl_multi_remove_handle($multiHandle, $handles[$type]);
        //     curl_close($handles[$type]);
        // }

        // curl_multi_close($multiHandle);
        // $complete();

        return 0;
    }

    private function initializeCurl(): void
    {
        $this->curlHandle = curl_init();
    }

    private function closeCurl(): void
    {
        curl_close($this->curlHandle);
    }

    private function setCurlOptions(): void
    {
        $headers = [];

        foreach ($this->request->getHeaders() as $header => $values) {
            $line = implode(',', $values);
            $headers[] = "$header: $line";
        }

        curl_setopt_array($this->curlHandle, $options = [
            CURLOPT_URL => (string)$this->request->getUri(),
            CURLOPT_CUSTOMREQUEST => $method = $this->request->getMethod(),
            CURLOPT_RETURNTRANSFER => $this->getReturnTransfer(),
            CURLOPT_FOLLOWLOCATION => $this->getFollowLocation(),
            CURLOPT_SSL_VERIFYPEER => $this->getSSLVerifyPeer(),
            CURLOPT_SSL_VERIFYHOST => $this->getSSLVerifyHost(),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($sslVersion = $this->getSSLVersion()) {
            $options[CURLOPT_SSLVERSION] = $sslVersion;
            curl_setopt($this->curlHandle, CURLOPT_SSLVERSION, $sslVersion);
        }

        if (($maxRedirects = $this->getMaxRedirects()) > 0) {
            $options[CURLOPT_MAXREDIRS] = $maxRedirects;
            curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS, $maxRedirects);
        }

        if (($timeout = $this->getTimeout()) > 0) {
            $options[CURLOPT_CONNECTTIMEOUT] = $timeout;
            $options[CURLOPT_TIMEOUT] = $timeout;
            curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $timeout);
        }

        if (!in_array($method, ['GET', 'HEAD'], true)) {
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $body = $this->request->getBody()->getContents());
            $options[CURLOPT_POSTFIELDS] = $body;
        }
    }

    /**
     * @throws HttpException
     */
    public function get(string $url, array $headers = null): HttpResponse
    {
        return $this->open('GET', $url, $headers)
            ->send();
    }

    /**
     * @throws HttpException
     */
    public function head(string $url, array $headers = null): HttpResponse
    {
        return $this->open('HEAD', $url, $headers)
            ->send();
    }

    /**
     * @throws HttpException
     */
    public function delete(string $url, array $headers = null): HttpResponse
    {
        return $this->open('DELETE', $url, $headers)
            ->send();
    }

    /**
     * @throws HttpException
     */
    public function post(string $url, mixed $postedData = null, array $headers = null): HttpResponse
    {
        $request = $this->open('POST', $url, $headers);
        if ($postedData !== null) {
            $request->setBody($postedData);
        }
        return $request->send();
    }

    /**
     * @throws HttpException
     */
    public function put(string $url, mixed $postedData = null, array $headers = null): HttpResponse
    {
        $request = $this->open('PUT', $url, $headers);
        if ($postedData !== null) {
            $request->setBody($postedData);
        }
        return $request->send();
    }

    /**
     * @throws HttpException
     */
    public function patch(string $url, mixed $postedData = null, array $headers = null): HttpResponse
    {
        $request = $this->open('PATCH', $url, $headers);
        if ($postedData !== null) {
            $request->setBody($postedData);
        }
        return $request->send();
    }

    /**
     * @return bool
     */
    public function getFollowLocation(): bool
    {
        return $this->getOption('follow_location', true);
    }

    /**
     * @param bool $followLocation
     */
    public function setFollowLocation(bool $followLocation): void
    {
        $this->options['follow_location'] = $followLocation;
    }

    public function getMaxRedirects(): int
    {
        return $this->getOption('max_redirects', 5);
    }

    public function setMaxRedirects(int $maxRedirects): void
    {
        $this->options['max_redirects'] = $maxRedirects;
    }

    public function getTimeout(): int
    {
        return $this->getOption('timeout', 10);
    }

    public function setTimeout(int $timeout): void
    {
        $this->options['timeout'] = $timeout;
    }

    private function getReturnTransfer()
    {
        return $this->getOption('return_transfer', true);
    }

    public function setReturnTransfer(bool $returnTransfer): void
    {
        $this->options['return_transfer'] = $returnTransfer;
    }

    public function getUserAgent(): string
    {
        return $this->getOption('user_agent');
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->options['user_agent'] = $userAgent;
    }

    public function getSSLVerifyPeer(): bool
    {
        return !!$this->getOption('ssl_verify_peer');
    }

    public function getSSLVerifyHost(): bool
    {
        return $this->getOption('ssl_verify_host');
    }

    public function getSSLVersion(): int
    {
        return (int)$this->getOption('ssl_version');
    }

    public function getDownloadRange(): ?string
    {
        if ($range = $this->getOption('range')) {
            if (is_array($range)) {
                return implode('-', $range);
            }
        }

        return is_string($range) ? $range : null;
    }

    public function setDownloadRange(int $offset, int $length): void
    {
        $this->options['range'] = [$offset, $offset + $length];
    }

    public function setCookieJar(CookieJar $cookieJar): void
    {
        $this->cookieJar = $cookieJar;
    }

    public function getCookieJar(): CookieJar
    {
        return $this->cookieJar;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    // 新增方法：获取 Cookie
    public function getCookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    // 新增方法：设置 Cookie
    public function setCookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        $this->cookieJar->setCookie(...func_get_args());
        $this->cookies[$name] = $value;
    }

    private function generateCookieHeader(): string
    {
        $cookieHeader = '';
        $url = $this->getOption('base_uri') ?: $this->request->getUri();
        $cookies = $this->cookieJar->getCookies($url);

        foreach ($cookies as $name => $value) {
            $cookieHeader .= "$name=$value; ";
        }

        return rtrim($cookieHeader, '; ');
    }

    private function processSetCookieHeader(ResponseInterface $response): void
    {
        $setCookieHeader = $response->getHeaderLine('Set-Cookie');
        if ($setCookieHeader !== '') {
            // 解析 Set-Cookie 头，更新 Cookie Jar
            $cookies = explode(';', $setCookieHeader);
            foreach ($cookies as $cookie) {
                $parts = explode('=', trim($cookie));
                $name = $parts[0];
                $value = $parts[1] ?? '';
                $this->cookieJar->setCookie($name, $value);
            }
        }
    }
}
