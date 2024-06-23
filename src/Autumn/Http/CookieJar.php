<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/02/2024
 */

namespace Autumn\Http;

use Psr\Http\Message\ResponseInterface;

class CookieJar
{
    private array $cookies = [];

    public function setCookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        $this->cookies[$domain][$path][$name] = [
            'value' => $value,
            'expires' => $expires,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
        ];
    }

    public function getCookies(string $url): array
    {
        $parsedUrl = parse_url($url);
        $domain = $parsedUrl['host'] ?? '';
        $path = $parsedUrl['path'] ?? '/';

        $matchedCookies = [];

        foreach ($this->cookies as $cookieDomain => $pathCookies) {
            if ($this->domainMatches($domain, $cookieDomain)) {
                foreach ($pathCookies as $cookiePath => $cookies) {
                    if ($this->pathMatches($path, $cookiePath)) {
                        foreach ($cookies as $name => $cookie) {
                            if ($this->cookieMatches($cookie, $domain, $path)) {
                                $matchedCookies[$name] = $cookie['value'];
                            }
                        }
                    }
                }
            }
        }

        return $matchedCookies;
    }

    private function domainMatches(string $domain, string $cookieDomain): bool
    {
        return $cookieDomain === $domain || ($cookieDomain[0] === '.' && strpos($domain, $cookieDomain) !== false);
    }

    private function pathMatches(string $path, string $cookiePath): bool
    {
        return strpos($path, $cookiePath) === 0;
    }

    private function cookieMatches(array $cookie, string $domain, string $path): bool
    {
        return (!$cookie['secure'] || $this->isSecureConnection()) &&
            (!$cookie['httpOnly'] || $this->isHttpOnlyRequest()) &&
            ($cookie['expires'] === 0 || $cookie['expires'] >= time()) &&
            $this->domainMatches($domain, key($this->cookies)) &&
            $this->pathMatches($path, key($this->cookies[key($this->cookies)]));
    }

    private function isSecureConnection(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    private function isHttpOnlyRequest(): bool
    {
        return !empty($_SERVER['HTTP_USER_AGENT']);
    }
}
