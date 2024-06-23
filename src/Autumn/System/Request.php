<?php

namespace Autumn\System;

use Autumn\Http\Message\ServerRequestTrait;
use Autumn\Http\Message\UploadedFile;
use Autumn\Http\Message\Uri;
use Autumn\Interfaces\ArrayInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Traversable;

class Request implements \ArrayAccess, ServerRequestInterface, ArrayInterface, \IteratorAggregate
{

    use ServerRequestTrait {
        withAttributes as public;
    }

    private static ?self $context = null;

    private ?array $parsedParams = null;

    private ?array $all = null;

    public function __construct(
        array $attributes = null,
        array $serverParams = null,
    )
    {
        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
        if (!empty($serverParams)) {
            $this->serverParams = $serverParams;
        }
    }

    public function getParsedParams(): array
    {
        $body = $this->getParsedBody();

        if (is_array($body)) {
            return $body;
        }

        if (is_object($body)) {
            // Iteration::flatten($this->getParsedBody()) ?? [];
            return get_object_vars($body);
        }

        return [];
    }

    public static function slug(): string
    {
        static $slug;
        if ($slug === null) {
            $path = $_POST['slug'] ?? $_GET['slug'] ?? $_REQUEST['slug'] ?? static::path();
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $slug = trim(substr($path, 0, -strlen($ext) - 1), " \n\r\t\v\0/\\");
        }
        return $slug;
    }

    public static function path(): string
    {
        static $path;
        return $path ??= static::capture()->getUri()->getPath();
    }

    public static function host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }

    public static function guess(): array
    {
        return [
            'host' => static::host(),
            'nonce' => static::nonce(),
            'signature' => static::requestBasicSignature(),
            'clientInfo' => static::clientInfo(),
        ];
    }

    public static function context(): static
    {
        return self::$context ??= static::capture();
    }

    public static function withContext(self $context): static
    {
        return self::$context = $context;
    }

    public static function capture(): static
    {
        if (self::$context !== null) {
            return self::$context;
        }

        $g = new static;

        $g->serverParams = self::captureServerParams();
        $g->cookieParams = self::captureCookieParams();
        $g->queryParams = self::captureQueryParams();
        $g->parsedBody = self::captureParsedBody();
        $g->uploadedFiles = self::captureUploadedFiles();
        $g->uri = self::captureRequestUri();
        $g->method = self::captureRequestMethod();
        $g->headers = self::captureRequestHeaders();
        $g->requestTarget = self::captureRequestTarget();
        $g->protocolVersion = self::captureRequestProtocolVersion();

        return $g;
    }

    public static function captureServerParams(): array
    {
        return $_SERVER;
    }

    public static function captureCookieParams(): array
    {
        return $_COOKIE;
    }

    public static function captureQueryParams(): array
    {
        return $_GET;
    }

    private static function captureRequestHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER ?? [] as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[static::formatHeaderName(substr($name, 5))][] = $value;
            }
        }
        return $headers;
    }

    public static function captureParsedBody(): array|object|null
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $input = self::captureInputData();
        if (!empty($input)) {
            if (self::isJsonContentType()) {
                return json_decode($input, true);
            }

            if (self::isXmlContentType()) {
                $data = simplexml_load_string($input);
                if ($data === false) {
                    throw new \InvalidArgumentException('Malformed XML payload');
                }
                return $data;
            }

            if (self::isMultipartFormDataContentType()) {
                $data = self::captureMultipartFormData($input);

                // Avoid overwriting $_FILES if already populated by PHP
                if (empty($_FILES) && isset($data['files'])) {
                    $_FILES = $data['files'];
                }

                return $data['params'] ?? [];
            }

            parse_str($input, $data);
            return $data;
        }

        return [];
    }

    public static function captureInputData(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    public static function captureUploadedFiles(): array
    {
        $uploadedFiles = [];
        foreach ($_FILES ?? [] as $upload) {
            if (is_array($upload['tmp_name'] ?? null)) {
                foreach ($upload['tmp_name'] as $index => $value) {
                    $uploadedFiles[] = new UploadedFile([
                        'tmp_name' => $value,
                        'error' => $upload['error'][$index],
                        'type' => $upload['type'][$index] ?? null,
                        'size' => $upload['size'][$index] ?? null,
                        'name' => $upload['name'][$index] ?? null,
                    ]);
                }
            } else {
                $uploadedFiles[] = new UploadedFile($upload);
            }
        }
        return $uploadedFiles;
    }

    public static function captureRequestUri(): UriInterface
    {
        $protocol = self::isHTTPS() ? 'https' : 'http';

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost';

        $uri = $_SERVER['REQUEST_URI'] ?? null;

        $url = $protocol . '://' . $host . $uri;

        return new Uri($url);
    }

    public static function captureRequestProtocolVersion(): string
    {
        return $_SERVER['SERVER_PROTOCOL'] ?? '';
    }

    public static function captureRequestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    }

    public static function captureRequestTarget(): string
    {
        $target = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['HTTP_X_ORIGINAL_URL'] ?? $_SERVER['REQUEST_URI'] ?? '';
        if ($target === '') {
            $target = $_SERVER['DOCUMENT_URI'] ?? null;
            if (!empty($_SERVER['QUERY_STRING'] ?? null)) {
                $target .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $target ?? '';
    }

    public static function captureMultipartFormData(string $inputData, string $boundary = null): array
    {
        if ($boundary === null) {
            $matches = [];
            preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '', $matches);
            $boundary = $matches[1] ?? null;
        }

        if ($boundary === null) {
            throw new \InvalidArgumentException('Boundary not found');
        }

        $blocks = explode("--$boundary", $inputData);

        // Remove empty blocks and closing boundary
        $blocks = array_filter(array_map('trim', $blocks));
        array_pop($blocks);

        $params = [];
        $files = [];

        foreach ($blocks as $block) {
            $boundaryIndex = strpos($block, "\r\n\r\n");
            if ($boundaryIndex === false) {
                continue;
            }

            $headers = substr($block, 0, $boundaryIndex);
            $content = substr($block, $boundaryIndex + 4);

            if (!str_contains($headers, 'filename')) {
                // Regular form field
                list($header, $value) = explode("\r\n\r\n", $block);
                preg_match('/name="(.*?)"/', $header, $matches);
                $params[$matches[1]] = substr($value, 0, -2); // Remove trailing \r\n
            } else {
                // File upload field
                if (!preg_match('/name="(.*?)"; filename="(.*?)"\r\nContent-Type: (.*?)\r\n/', $headers, $matches)) {
                    continue;
                }

                $name = $matches[1] ?? null;
                $filename = $matches[2] ?? null;
                $type = $matches[3] ?? null;

                $error = UPLOAD_ERR_OK;
                $size = strlen($content);

                if ($iniMaxUploadSize = Number::parseBytes(ini_get('upload_max_filesize'))) {
                    if ($iniMaxUploadSize < $size) {
                        $error = UPLOAD_ERR_INI_SIZE;
                    }
                }

                $tempFile = null;
                $tempDir = sys_get_temp_dir();
                if (empty($tempDir)) {
                    $error = UPLOAD_ERR_NO_TMP_DIR;
                } else {
                    $tempFile = tempnam($tempDir, 'tmp_');
                    if ($tempFile === false) {
                        $error = UPLOAD_ERR_NO_FILE;
                    } // Write content to the temporary file
                    elseif (file_put_contents($files[$name]['tmp_name'], $content) === false) {
                        $error = UPLOAD_ERR_CANT_WRITE;
                    }
                }

                // to do: complete the following error capture.
//                define('UPLOAD_ERR_FORM_SIZE', 2);
//                define('UPLOAD_ERR_PARTIAL', 3);

                // Save file data to $_FILES format
                $files[$name] = [
                    'name' => $filename,
                    'type' => $type,
                    'tmp_name' => $tempFile,
                    'error' => $error,
                    'size' => $size
                ];
            }
        }

        return ['params' => $params, 'files' => $files];
    }

    public static function isHTTPS(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public static function isJsonContentType(): bool
    {
        return strcasecmp($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/json') === 0;
    }

    public static function isXmlContentType(): bool
    {
        return strcasecmp($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'text/xml') === 0;
    }

    public static function isMultipartFormDataContentType(): bool
    {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'multipart/form-data');
    }

    public static function isAcceptJSON(): bool
    {
        return ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
    }

    public static function isAcceptXML(): bool
    {
        return ($_SERVER['HTTP_ACCEPT'] ?? '') === 'text/xml';
    }

    public static function clientInfo(): ?array
    {
        static $clientInfo;

        if ($clientInfo === null) {
            $clientInfo = false;

            if (is_array($clientInfo = $_SERVER['HTTP_CLIENT_INFO'] ?? null)) {
                if (is_string($checkSum = $_SERVER['HTTP_CLIENT_INFO_CHECKSUM'])
                    && (strlen($checkSum) === 32)) {
                    if ($checkSum === md5(serialize($clientInfo))) {
                        return $clientInfo;
                    }
                }
            }
        }

        return $clientInfo ?: null;
    }

    /**
     * Generate a basic request signature.
     * This signature includes host, user agent, remote address, and a nonce.
     * It first tries to get the signature from $_COOKIE['req_sign'], if available.
     * If not available, it generates a new signature.
     *
     * @return string The generated request signature.
     */
    public static function requestBasicSignature(): string
    {
        static $signature;

        // Check if signature exists in $_COOKIE, otherwise generate a new one
        return $signature ??= ($_COOKIE['req_sign'] ?? null) ?: static::generateBasicSignature();
    }

    public static function generateBasicSignature(): string
    {
        return md5(serialize([
            static::host(),
            static::userAgent(),
            static::remoteAddress(),
            static::nonce()
        ]));
    }

    /**
     * Generate a nonce.
     * This method first tries to get nonce from $_COOKIE['req_nonce'].
     * If not available, it generates a new random nonce.
     *
     * @return string The generated nonce.
     */
    public static function nonce(): string
    {
        static $nonce;

        // Check if nonce exists in $_COOKIE, otherwise generate a new one
        return $nonce ??= ($_COOKIE['req_nonce'] ?? null) ?: static::generateNonce();
    }

    public static function generateNonce(): string
    {
        return md5(mt_rand() . microtime(true));
    }

    /**
     * Retrieves the request time as a \DateTimeInterface object.
     *
     * @return \DateTimeInterface|null The request time as a \DateTimeInterface object, or null if not available.
     */
    public static function requestTime(): ?\DateTimeInterface
    {
        static $time;

        if (!isset($time)) {
            $time = $_SERVER['REQUEST_TIME_FLOAT'] ?? $_SERVER['REQUEST_TIME'] ?? null;
            if ($time !== null) {
                $time = Date::of($time);
            } else {
                $time = false;
            }
        }

        return $time ?: null;
    }

    /**
     * Get the referrer page url
     *
     * @return string|null
     */
    public static function referrer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    /**
     * Get the User Agent of client-end browser.
     *
     * @return string|null
     */
    public static function userAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Get the remote IP address(es).
     *
     * This function retrieves the IP address(es) of the client making the request. It supports both IPv4 and IPv6 addresses
     * and handles cases where the client is behind one or more proxies.
     *
     * @param bool $realOneOnly Whether to return only the most real IP address.
     * @param bool $allowIPv4 Whether to include IPv4 addresses in the result.
     * @param bool $allowIPv6 Whether to include IPv6 addresses in the result.
     * @param bool $forceResetCache Whether force to reset the cached IP addresses.
     * @return string|array|null Returns the IP address(es) based on the function parameters.
     */
    public static function remoteAddress(bool $realOneOnly = false,
                                         bool $allowIPv4 = true,
                                         bool $allowIPv6 = true,
                                         bool $forceResetCache = null
    ): string|array|null
    {
        static $cacheIPv4, $cacheIPv6, $cacheIpAll;

        // If the cache is not initialized, populate it
        if (!isset($cacheIpAll) || $forceResetCache) {
            // Initialize caches
            $cacheIPv4 = [];
            $cacheIPv6 = [];
            $cacheIpAll = [];

            // Headers to check for IP addresses
            $ipHeaders = [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            ];

            $index = 0;
            // Loop through the headers to extract IP addresses
            foreach ($ipHeaders as $header) {
                // Check if the header exists in $_SERVER
                if ($ipValue = $_SERVER[$header] ?? null) {
                    // Explode the header value to handle multiple IPs
                    $ipList = explode(',', $ipValue);
                    foreach ($ipList as $ip) {
                        // Trim the IP address
                        if ($ip = trim($ip)) {
                            // Validate and store IPv4 addresses
                            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                                $cacheIPv4[$index] = $ip;
                                $cacheIpAll[$index++] = $ip;
                            } // Validate and store IPv6 addresses
                            elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                                $cacheIPv6[$index] = $ip;
                                $cacheIpAll[$index++] = $ip;
                            }
                        }
                    }
                }
            }
        }

        // Return the result based on the function parameters
        if ($realOneOnly) {
            $index = array_key_last($cacheIpAll);
            return ($allowIPv4 ? $cacheIPv4[$index] ?? null : null)
                ?: ($allowIPv6 ? $cacheIPv6[$index] ?? null : null);
        }

        return $allowIPv4
            ? ($allowIPv6 ? $cacheIpAll : array_values($cacheIPv4))
            : ($allowIPv6 ? array_values($cacheIPv6) : null);
    }

    /**
     * Retrieves the most real IP address based on the provided parameters.
     *
     * @param bool $allowIPv4 Whether to include IPv4 addresses.
     * @param bool $allowIPv6 Whether to include IPv6 addresses.
     * @return string|null The most real IP address, or null if not found.
     */
    public static function realIP(bool $allowIPv4 = true, bool $allowIPv6 = false): ?string
    {
        return static::remoteAddress(true, $allowIPv4, $allowIPv6);
    }

    /**
     * Retrieves the most real IPv4 address.
     *
     * @return string|null The most real IPv4 address, or null if not found.
     */
    public static function realIPv4(): ?string
    {
        return static::remoteAddress(true, true, false);
    }

    /**
     * Retrieves the most real IPv6 address.
     *
     * @return string|null The most real IPv6 address, or null if not found.
     */
    public static function realIPv6(): ?string
    {
        return static::remoteAddress(true, false, true);
    }

    /**
     * Retrieves all IP proxies from the request headers.
     *
     * @return array An array containing all IP proxies.
     */
    public static function ipProxies(): array
    {
        $chains = static::remoteAddress(false, true, true);
        array_pop($chains); // Exclude the client's IP address
        return $chains;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset])
            || isset($this->queryParams[$offset])
            || (is_object($this->parsedBody)
                ? isset($this->parsedBody->$offset)
                : isset($this->parsedBody[$offset])
            );
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (isset($this->attributes[$offset])) {
            return $this->attributes[$offset];
        }

        if (is_array($this->parsedBody)) {
            if (isset($this->parsedBody[$offset])) {
                return $this->parsedBody[$offset];
            }
        }

        if (is_object($this->parsedBody)) {
            if (isset($this->parsedBody->$offset)) {
                return $this->parsedBody->$offset;
            }
        }

        return $this->queryParams[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException('Unable to set a value to an immutable Request object.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException('Unable to remove an entry off an immutable Request object.');
    }

    public function toArray(): array
    {
        if ($this->all === null) {
            if ($this->parsedBody instanceof ArrayInterface) {
                $params = $this->parsedBody->toArray();
            } elseif (is_object($this->parsedBody)) {
                $params = get_object_vars($this->parsedBody);
            } else {
                $params = (array)$this->parsedBody;
            }

            $this->all = $this->attributes + $params + $this->queryParams;
        }

        return $this->all;
    }

    public function getAuthUser(): ?array
    {
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        if (empty($username)) {
            $username = $this->get('username', fromAttribute: false);
            if (empty($username)) {
                return null;
            }

            return [
                'username' => $username,
                'password' => $this->get('password', fromAttribute: false)
            ];
        }

        return [
            'username' => $username,
            'password' => $_SERVER['PHP_AUTH_PW'] ?? null,
        ];
    }

    public function getCredentials(): ?array
    {
        $authentication = $this->getHeaderLine('Authorization');
        if (empty($authentication)) {
            return null;
        }

        [$digest, $credentials] = explode(' ', $authentication);
        $result = compact('digest', 'credentials');

        if (strtolower($digest) == 'basic') {
            $decoded = base64_decode($credentials);
            [$username, $password] = explode(':', $decoded);
            return $result + compact('username', 'password');
        }

        return $result;
    }

    public function getFromParsedBody(string $name, mixed $default = null): mixed
    {
        if (is_array($this->parsedBody)) {
            return $this->parsedBody[$name] ?? $default;
        } elseif ($this->parsedBody instanceof \ArrayAccess) {
            return $this->parsedBody->$name ?? $this->parsedBody[$name] ?? $default;
        } elseif (is_object($this->body)) {
            return $this->parsedBody->$name ?? $default;
        }

        return $default;
    }

    public function getFromQueryParams(string $name, mixed $default = null): mixed
    {
        return $this->queryParams[$name] ?? $default;
    }

    public function getFromCookies(string $name, mixed $default = null): mixed
    {
        return $this->cookieParams[$name] ?? $default;
    }

    public function getFromHeaders(string $name, mixed $default = null): mixed
    {
        return $this->headers[self::formatHeaderName($name)]
            ?? $this->headers[self::formatHeaderName('http_' . $name)]
            ?? $default;
    }

    public function has(string $name, bool $fromAttribute = true, bool $fromCookie = false, bool $fromHeader = false): bool
    {
        if ($fromAttribute) {
            if (isset($this->attributes[$name])) {
                return true;
            }
        }

        if (isset($this->queryParams[$name])) {
            return true;
        }

        if (is_array($this->parsedBody) || ($this->parsedBody instanceof \ArrayAccess)) {
            if (isset($this->parsedBody[$name])) {
                return true;
            }
        } else {
            if (isset($this->parsedBody->$name)) {
                return true;
            }
        }

        if ($fromCookie) {
            if (isset($this->cookieParams[$name])) {
                return true;
            }
        }

        if ($fromHeader && ($headerName = self::formatHeaderName($name))) {
            if (isset($this->headers[$headerName])) {
                return true;
            }

            $headerName = self::formatHeaderName('http_' . $name);
            if (isset($this->headers[$headerName])) {
                return true;
            }
        }

        return false;
    }

    public function get(string $name, mixed $default = null, bool $fromAttribute = true, bool $fromCookie = false, bool $fromHeader = false): mixed
    {
        $value = null;

        if ($fromAttribute) {
            $value = $this->getAttribute($name);
        }

        if ($value === null) {
            $value = $this->getFromParsedBody($name);
        }

        if ($value === null) {
            $value = $this->getFromQueryParams($name);
        }

        if ($value === null && $fromCookie) {
            $value = $this->getFromCookies($name);
        }

        if ($value === null && $fromHeader) {
            $value = $this->getFromHeaders($name);
            if (is_array($value)) {
                return implode(',', $value);
            }
        }

        return $value ?? $default;
    }

    public function any(string ...$keys): mixed
    {
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function some(string ...$keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key);
        }

        return $data;
    }

    public function contains(string ...$keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }

        return $data;
    }

//    public function getId(string $key, int $default = null): ?int
//    {
//        $value = $this->get($key);
//        if ($value !== null && $value !== '') {
//            return Assert::id(Number::int($value), sprintf(
//                'The value of parameter "%s" is not an ID type', $key
//            ));
//        } else {
//            return $default;
//        }
//    }
//
//    public function getInt(string $key, int $default = null): ?int
//    {
//        $value = $this->get($key);
//        if ($value !== null && $value !== '') {
//            return Number::int($value) ?? Assert::int($value, sprintf(
//                'The value of parameter "%s" is not an integer type', $key
//            ));
//        } else {
//            return $default;
//        }
//    }
//
//    public function getFloat(string $key, float $default = null): ?float
//    {
//        $value = $this->get($key);
//        if ($value !== null && $value !== '') {
//            $value = Number::numeric($value);
//            return Assert::float($value, sprintf(
//                'The value of parameter "%s" is not a float type', $key
//            ));
//        } else {
//            return $default;
//        }
//    }
//
//    public function getBool(string $key, bool $default = null): ?bool
//    {
//        $value = $this->get($key);
//        if ($value !== null && $value !== '') {
//            return Assert::bool($value, sprintf(
//                'The value of parameter "%s" is not a boolean type', $key
//            ));
//        } else {
//            return $default;
//        }
//    }
//
//    public function getArray(string $key, string $separator = null, array $default = null): ?array
//    {
//        $value = $this->get($key);
//        if (is_array($value)) {
//            return $value;
//        }
//
//        if ($value === '' || $value === null) {
//            return $default;
//        }
//
//        if (!empty($separator)) {
//            $pattern = '#[' . $separator . ']+#';
//            return preg_split($pattern, (string)$value);
//        }
//
//        return [$value];
//    }
//
//    public function split(string $key, string $separator = ',', array $default = null): ?array
//    {
//        return $this->getArray($key, $separator, $default);
//    }
//
//    public function getDateTime(string $key, \DateTimeInterface $default = null): ?\DateTimeInterface
//    {
//        $value = $this->get($key);
//        if ($value !== null && $value !== '') {
//            return Date::of($value);
//        }
//        return $default;
//    }
//
//    public function getString(string $key, string $default = null): ?string
//    {
//        $value = $this->get($key);
//        return ($value === null) ? $default : Assert::stringable($value, sprintf(
//            'The value of parameter "%s" is not a stringable type.', $key
//        ));
//    }

    public function fullUrlOf(string $path, string|array $query = null, string $fragment = null): string
    {
        if (is_array($query)) {
            $query = http_build_query($query);
        }

        $uri = $this->getUri()->withPath($path)->withQuery($query ?? '')->withFragment($fragment ?? '');

        return (string)Uri::fromInterface($uri);
    }

    public function fullUrlWithQuery(string|array $query = null, bool $append = null): UriInterface
    {
        $uri = $this->getUri();
        if ($append) {
            parse_str($uri->getQuery(), $args);

            if (is_string($query)) {
                parse_str($query, $temp);
                $query = $temp;
            }

            if ($query) {
                $query = array_merge($query, $args);
            }
        }

        if (is_array($query)) {
            $query = http_build_query($query);
        }

        $result = $uri->withQuery($query);
        if (!method_exists($result, '__toString')) {
            return Uri::fromInterface($result);
        }
        return $uri;
    }

    public function getIterator(): Traversable
    {
        $passed = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (!in_array($key, $passed)) {
                $passed[] = $key;
                yield $key => $value;
            }
        }

        if ($body = $this->getParsedBody()) {
            if (!is_iterable($body)) {
                $body = get_object_vars($body);
            }

            foreach ($body as $key => $value) {
                if (!in_array($key, $passed)) {
                    $passed[] = $key;
                    yield $key => $value;
                }
            }
        }

        foreach ($this->getQueryParams() as $key => $value) {
            if (!in_array($key, $passed)) {
                $passed[] = $key;
                yield $key => $value;
            }
        }
    }

//    public function separate(array $properties, bool $avoidNull = null, bool $remain = null): array
//    {
//        $data = [];
//        foreach ($properties as $property => $type) {
//            if (is_int($property)) {
//                $property = $type;
//                $type = '';
//            }
//
//            if (!method_exists($this, $func = 'get' . $type)) {
//                throw new \InvalidArgumentException(sprintf(
//                    'Unknown type of property "%s".', $property
//                ));
//            }
//
//            $value = $this->$func($property);
//            if (($value !== null) || !$avoidNull) {
//                $data[$property] = $value;
//            }
//
//            if (!$remain) {
//                $this->setAttribute($property, null);
//            }
//        }
//
//        return $data;
//    }

//    /**
//     * @throws \ReflectionException
//     */
//    public function assign(string|ModelInterface $model, string $param = null): ModelInterface
//    {
//        if (is_string($model)) {
//            if (!is_subclass_of($model, ModelInterface::class, true)) {
//                throw ValidationException::of('The class `%s` is not a ModelInterface.');
//            }
//
//            $model = new $model;
//        }
//
//        if ($param) {
//            $inputs = $this->get($param);
//            if (!is_array($inputs)) {
//                return $model;
//            }
//        } else {
//            $inputs = $this;
//        }
//
//        foreach (Reflection::fields($model) as $property) {
//            $propertyName = $property->getName();
//            if ($setter = Reflection::setter($model, $propertyName)) {
//                $value = $inputs[$propertyName] ?? null;
//
//                if ($value === null) {
//                    $snakeName = Str::toSnakeCase($propertyName);
//                    if ($snakeName !== $propertyName) {
//                        $value = $inputs[$snakeName] ?? null;
//                    }
//                }
//
//                if ($value !== null) {
//                    $setter->invoke($model, $value);
//                }
//            }
//        }
//
//        return $model;
//    }
}
