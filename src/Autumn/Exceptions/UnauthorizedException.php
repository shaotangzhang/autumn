<?php
namespace Autumn\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Class UnauthorizedException
 *
 * A server using HTTP authentication will respond with a 401 Unauthorized response to a request for a protected
 * resource. This response must include at least one WWW-Authenticate header and at least one challenge, to
 * indicate what authentication schemes can be used to access the resource (and any additional data that each
 * particular scheme needs).
 *
 * Multiple challenges are allowed in one WWW-Authenticate header, and multiple WWW-Authenticate headers are
 * allowed in one response. A server may also include the WWW-Authenticate header in other response messages to
 * indicate that supplying credentials might affect the response.
 *
 * After receiving the WWW-Authenticate header, a client will typically prompt the user for credentials, and then
 * re-request the resource. This new request uses the Authorization header to supply the credentials to the server,
 * encoded appropriately for the selected "challenge" authentication method. The client is expected to select the
 * most secure of the challenges it understands (note that in some cases the "most secure" method is debatable).
 *
 * @package     Autumn\Exceptions
 * @since       1/04/2024
 */
class UnauthorizedException extends RequestException
{
    public const ERROR_CODE = 401;
    public const ERROR_MESSAGE = 'Unauthorized';

    private string $challenge = '';

    /**
     * @return string
     */
    public function getChallenge(): string
    {
        return $this->challenge;
    }

    /**
     * @param string $challenge
     * @return UnauthorizedException
     */
    public function withChallenge(string $challenge): static
    {
        if ($this->challenge === $challenge) {
            return $this;
        }

        $clone = clone $this;
        $clone->challenge = $challenge;
        return $clone;
    }

    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        if ($challenge = $this->getChallenge()) {
            return $response->withHeader('WWW-Authenticate', $challenge);
        }

        return $response;
    }
}