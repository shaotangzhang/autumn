<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/12/2023
 */

namespace Autumn\Http\Message;

use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 *
 * A class that implements UriInterface and handles URL parsing and manipulation.
 */
class Uri implements UriInterface, \Stringable
{
    /**
     * The pattern to validate a URL
     */
    public const PATTERN_URL = '/^(?P<scheme>[a-zA-Z][a-zA-Z0-9+.-]*):\/\/(?P<domain>(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(?::(?P<port>\d+))?(?P<path>\/[^?\s#]*)?(?:\?(?P<query>[^\s#]*))?(?:#(?P<fragment>[^\s]*))?$/';

    private array $uriInfo = [];
    private string $uri = '';

    /**
     * Uri constructor.
     *
     * @param string|null $uri The URI string to initialize.
     */
    public function __construct(string $uri = null)
    {
        if (!empty($uri)) {
            $this->setUri($uri);
        }
    }

    /**
     * Creates an instance of Uri from an existing UriInterface.
     *
     * @param UriInterface $result An existing UriInterface instance.
     * @return static A new Uri instance.
     */
    public static function fromInterface(UriInterface $result): static
    {
        if ($result instanceof static) {
            return $result;
        }

        if ($result instanceof self) {
            return new static($result->uri);
        }

        $instance = new static;
        $instance->uriInfo = [
            'scheme' => $result->getScheme(),
            'host' => $result->getHost(),
            'port' => $result->getPort(),
            'userInfo' => $result->getUserInfo(),
            'authority' => $result->getAuthority(),
            'path' => $result->getPath(),
            'query' => $result->getQuery(),
            'fragment' => $result->getFragment()
        ];

        return $instance;
    }

    /**
     * Checks if a link is a URL
     *
     * @param string $link
     * @param string ...$allowedSchemes
     * @return bool
     */
    public static function isURL(string $link, string ...$allowedSchemes): bool
    {
        if (!$link = trim($link)) {
            return false;
        }

        $schemePattern = $allowedSchemes ? '(?:' . implode('|', array_map(fn($v) => preg_quote($v, '#'), $allowedSchemes)) . ')' : '[a-zA-Z][a-zA-Z0-9+.-]*';

        $pattern = '/^' . $schemePattern . ':\/\/(?P<domain>(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(?::(?P<port>\d+))?(?P<path>\/[^?\s#]*)?(?:\?(?P<query>[^\s#]*))?(?:#(?P<fragment>[^\s]*))?$/';

        // 执行匹配
        return preg_match($pattern, $link);
    }

    /**
     * Combines a URI with a base URI.
     *
     * @param string $uri The URI to combine.
     * @param string|self $base The base URI.
     * @param bool|null $combineQueryArgs Whether to combine query arguments.
     * @return string The combined URI.
     */
    public static function combineURI(string $uri, string|self $base, bool $combineQueryArgs = null): string
    {
        if (is_string($base)) {
            $base = new self($base);
        }

        return $base->combine($uri, $combineQueryArgs)->__toString();
    }

    /**
     * Combines and normalizes a URI, may be with a base URI.
     *
     * @param string $uri The URI to combine.
     * @param string|Uri|null $base The base URI.
     * @param bool|null $combineQueryArgs Whether to combine query arguments.
     * @return string The combined URI.
     */
    public static function normalizeURI(string $uri, string|self $base = null, bool $combineQueryArgs = null): string
    {
        if ($base) {
            if (is_string($base)) {
                $base = new self($base);
            }

            return $base->combine($uri, $combineQueryArgs)->normalize();
        }

        return (new self($uri))->normalize();
    }

    /**
     * Combines multiple query arguments.
     *
     * @param string|array|null $query The base query string or array.
     * @param string|array|null ...$args Additional query strings or arrays to combine.
     * @return string The combined query string.
     */
    public static function combineQueryArgs(string|array|null $query, string|array|null ...$args): string
    {
        $params = $query ?? [];
        if (is_string($query)) {
            parse_str($query, $params);
        }

        foreach ($args as $arg) {
            if ($a = $arg) {
                if (is_string($arg)) {
                    parse_str($arg, $a);
                }

                $params = array_merge($params, $a);
            }
        }

        return http_build_query($params);
    }

    /**
     * Combines a base path with another path.
     *
     * @param string $base The base path.
     * @param string $path The path to combine with the base.
     * @return string The combined path.
     */
    public static function combinePath(string $base, string $path): string
    {
        if (!($path = trim($path))) {
            return $path;
        }

        $path = strtr($path, '\\', '/');
        if (str_starts_with($path, '/')) {
            return $path;
        }

        $baseParts = explode('/', strtr($base, '\\', '/'));
        array_pop($baseParts);

        foreach (explode('/', $path) as $part) {
            if (($part === '.') || (trim($part) === '')) {
                continue;
            }

            if ($part === '..') {
                if (!empty($baseParts)) {
                    array_pop($baseParts);
                }
            } else {
                $baseParts[] = $part;
            }
        }

        return implode('/', $baseParts);
    }

    /**
     * Returns the URI as a string.
     *
     * @return string The URI string.
     */
    public function getUri(): string
    {
        return $this->__toString();
    }

    /**
     * Sets the URI and parses it.
     *
     * @param string $uri The URI to set.
     */
    private function setUri(string $uri): void
    {
        $this->uri = $uri;
        $this->uriInfo = parse_url($uri);
    }

    /**
     * Converts the URI object to a string.
     *
     * @return string The URI string.
     */
    public function __toString(): string
    {
        if ($this->uri !== '') {
            return $this->uri;
        }

        $uri = '';

        $scheme = $this->getScheme();
        if ($scheme !== '') {
            $uri .= $scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $path = $this->getPath();
        if ($path = trim($path)) {
            if ($uri && !str_ends_with($uri, '/')) {
                $uri .= '/';
            }
            $uri .= ltrim($path, '/\\');
        }

        $query = $this->getQuery();
        if ($query !== '') {
            $uri .= '?' . $query;
        }

        $fragment = $this->getFragment();
        if ($fragment !== '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * Normalizes the URI by ensuring the path uses forward slashes.
     *
     * @return string The normalized URI.
     */
    public function normalize(): string
    {
        if ($path = trim($this->getPath())) {
            $path = preg_replace('#[/\\\\]+#', '/', $path);
            $this->uriInfo['path'] = $path;
            $this->uri = '';
        }

        return $this->__toString();
    }

    /**
     * Combines the current URI with another URI.
     *
     * @param string $link The URI to combine.
     * @param bool|null $combineArgs Whether to combine query arguments.
     * @return static The combined URI.
     */
    public function combine(string $link, bool $combineArgs = null): static
    {
        $that = new static($link);

        if ($thatScheme = $that->getScheme()) {
            if ($thisScheme = $this->getScheme()) {
                if ($thisScheme !== $thatScheme) {
                    return $that;
                }
            }
        }

        if ($thatAuthority = $that->getAuthority()) {
            if ($thisAuthority = $this->getAuthority()) {
                if ($thisAuthority !== $thatAuthority) {
                    return $that;
                }
            }
        }

        if (!$thatScheme) {
            $that->uriInfo['scheme'] = $thisScheme ?? $this->getScheme();
        }

        if (!$thatAuthority) {
            $that->uriInfo['authority'] = $thisAuthority ?? $this->getAuthority();
        }

        $that->uriInfo['path'] = $this->combinePath($this->getPath(), $that->getPath());

        if ($combineArgs) {
            $that->uriInfo['query'] = $this->combineQueryArgs($that->getQuery(), $this->getQuery());
        }

        $that->uri = '';
        return $that;
    }

    /**
     * Gets the scheme component of the URI.
     *
     * @return string The scheme component.
     */
    public function getScheme(): string
    {
        return $this->uriInfo['scheme'] ?? '';
    }

    /**
     * Gets the authority component of the URI.
     *
     * @return string The authority component.
     */
    public function getAuthority(): string
    {
        if (!isset($this->uriInfo['authority'])) {
            $authority = '';

            $userInfo = $this->getUserInfo();
            $host = $this->getHost();
            $port = $this->getPort();

            if ($userInfo !== '') {
                $authority .= $userInfo . '@';
            }

            if ($host !== '') {
                $authority .= $host;
            }

            if ($port !== null) {
                $authority .= ':' . $port;
            }

            $this->uriInfo['authority'] = $authority;
        }

        return $this->uriInfo['authority'];
    }

    /**
     * Gets the user info component of the URI.
     *
     * @return string The user info component.
     */
    public function getUserInfo(): string
    {
        if (!isset($this->uriInfo['userInfo'])) {
            $userInfo = $this->uriInfo['user'] ?? '';
            if ($userInfo !== '') {
                $pass = $this->uriInfo['pass'] ?? $this->uriInfo['password'] ?? $this->uriInfo['pwd'] ?? '';
                if ($pass !== '') {
                    $userInfo .= ":$pass";
                }
            }
            $this->uriInfo['userInfo'] = $userInfo;
        }

        return $this->uriInfo['userInfo'];
    }

    /**
     * Gets the host component of the URI.
     *
     * @return string The host component.
     */
    public function getHost(): string
    {
        return $this->uriInfo['host'] ?? '';
    }

    /**
     * Gets the port component of the URI.
     *
     * @return int|null The port component.
     */
    public function getPort(): ?int
    {
        return isset($this->uriInfo['port']) ? (int)$this->uriInfo['port'] : null;
    }

    /**
     * Gets the path component of the URI.
     *
     * @return string The path component.
     */
    public function getPath(): string
    {
        return $this->uriInfo['path'] ?? '';
    }

    /**
     * Gets the query component of the URI.
     *
     * @return string The query component.
     */
    public function getQuery(): string
    {
        return $this->uriInfo['query'] ?? '';
    }

    /**
     * Gets the fragment component of the URI.
     *
     * @return string The fragment component.
     */
    public function getFragment(): string
    {
        return $this->uriInfo['fragment'] ?? '';
    }

    /**
     * Returns an instance with the specified scheme.
     *
     * @param string $scheme The scheme to set.
     * @return static A new instance with the specified scheme.
     */
    public function withScheme(string $scheme): static
    {
        if ($this->getScheme() === $scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['scheme'] = $scheme;
        $clone->uri = '';
        return $clone;
    }

    /**
     * Returns an instance with the specified user info.
     *
     * @param string $user The user info to set.
     * @param string|null $password The password to set.
     * @return static A new instance with the specified user info.
     */
    public function withUserInfo(string $user, ?string $password = null): static
    {
        if ($user !== '') {
            $userInfo = $user . ($password ? ":$password" : '');
        } else {
            $userInfo = '';
        }

        if ($userInfo === $this->getUserInfo()) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['userInfo'] = $userInfo;
        $clone->uri = '';
        unset($clone->uriInfo['authority']);
        return $clone;
    }

    /**
     * Returns an instance with the specified host.
     *
     * @param string $host The host to set.
     * @return static A new instance with the specified host.
     */
    public function withHost(string $host): static
    {
        if ($this->getHost() === $host) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['host'] = $host;
        $clone->uri = '';
        unset($clone->uriInfo['authority']);
        return $clone;
    }

    /**
     * Returns an instance with the specified port.
     *
     * @param int|null $port The port to set.
     * @return static A new instance with the specified port.
     */
    public function withPort(?int $port): static
    {
        if ($port === $this->getPort()) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['port'] = $port;
        $clone->uri = '';
        unset($clone->uriInfo['authority']);
        return $clone;
    }

    /**
     * Returns an instance with the specified path.
     *
     * @param string $path The path to set.
     * @return static A new instance with the specified path.
     */
    public function withPath(string $path): static
    {
        if ($path === $this->getPath()) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['path'] = $path;
        $clone->uri = '';
        return $clone;
    }

    /**
     * Returns an instance with the specified query.
     *
     * @param string $query The query to set.
     * @return static A new instance with the specified query.
     */
    public function withQuery(string $query): static
    {
        if ($query === $this->getQuery()) {
            return $this;
        }

        $clone = clone $this;
        $clone->uriInfo['query'] = $query;
        $clone->uri = '';
        return $clone;
    }

    /**
     * Returns an instance with the specified fragment.
     *
     * @param string $fragment The fragment to set.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment(string $fragment): static
    {
        if ($fragment === $this->getFragment()) {
            return $this;
        }

        $clone = clone $this;

        $clone->uriInfo['fragment'] = $fragment;
        if (!isset($clone->uriInfo['fragment'])) {
            $clone->uri = rtrim($clone->uri, '#') . '#' . rawurlencode($fragment);
        } else {
            $clone->uri = '';
        }

        return $clone;
    }
}