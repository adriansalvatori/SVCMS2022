<?php

namespace ProjectHuddle\Vendor\Laminas\Http;

use ArrayIterator;
use ProjectHuddle\Vendor\Laminas\Http\Header\SetCookie;
use ProjectHuddle\Vendor\Laminas\Http\Headers;
use ProjectHuddle\Vendor\Laminas\Uri;

use function array_keys;
use function array_merge;
use function count;
use function is_array;
use function is_string;
use function sprintf;
use function strrpos;
use function substr;

/**
 * A Laminas\Http\Cookies object is designed to contain and maintain HTTP cookies, and should
 * be used along with Laminas\Http\Client in order to manage cookies across HTTP requests and
 * responses.
 *
 * The class contains an array of Laminas\Http\Header\Cookie objects. Cookies can be added
 * automatically from a request or manually. Then, the Cookies class can find and return the
 * cookies needed for a specific HTTP request.
 *
 * A special parameter can be passed to all methods of this class that return cookies: Cookies
 * can be returned either in their native form (as Laminas\Http\Header\Cookie objects) or as strings -
 * the later is suitable for sending as the value of the "Cookie" header in an HTTP request.
 * You can also choose, when returning more than one cookie, whether to get an array of strings
 * (by passing Laminas\Http\Client\Cookies::COOKIE_STRING_ARRAY) or one unified string for all cookies
 * (by passing Laminas\Http\Client\Cookies::COOKIE_STRING_CONCAT).
 *
 * @link       http://wp.netscape.com/newsref/std/cookie_spec.html for some specs.
 */
class Cookies extends Headers
{
    /**
     * Return cookie(s) as a Laminas\Http\Cookie object
     */
    public const COOKIE_OBJECT = 0;

    /**
     * Return cookie(s) as a string (suitable for sending in an HTTP request)
     */
    public const COOKIE_STRING_ARRAY = 1;

    /**
     * Return all cookies as one long string (suitable for sending in an HTTP request)
     */
    public const COOKIE_STRING_CONCAT = 2;

    /**
     * Return all cookies as one long string (strict mode)
     *  - Single space after the semi-colon separating each cookie
     *  - Remove trailing semi-colon, if any
     */
    public const COOKIE_STRING_CONCAT_STRICT = 3;

    /** @var array */
    protected $cookies = [];

    /** @var Headers */
    protected $headers;

    /** @var array */
    protected $rawCookies;

    /**
     * @static
     * @throws Exception\RuntimeException
     * @param string $string
     * @return void
     */
    public static function fromString($string)
    {
        throw new Exception\RuntimeException(
            self::class . '::' . __FUNCTION__ . ' should not be used as a factory, use '
            . __NAMESPACE__ . '\Headers::fromString() instead.'
        );
    }

    /**
     * Add a cookie to the class. Cookie should be passed either as a Laminas\Http\Header\SetCookie object
     * or as a string - in which case an object is created from the string.
     *
     * @param SetCookie|string $cookie
     * @param Uri\Uri|string $refUri Optional reference URI (for domain, path, secure)
     * @throws Exception\InvalidArgumentException
     */
    public function addCookie($cookie, $refUri = null)
    {
        if (is_string($cookie)) {
            $cookie = SetCookie::fromString($cookie, $refUri);
        }

        if ($cookie instanceof SetCookie) {
            $domain = $cookie->getDomain();
            $path   = $cookie->getPath();
            if (! isset($this->cookies[$domain])) {
                $this->cookies[$domain] = [];
            }
            if (! isset($this->cookies[$domain][$path])) {
                $this->cookies[$domain][$path] = [];
            }
            $this->cookies[$domain][$path][$cookie->getName()] = $cookie;
            $this->rawCookies[]                                = $cookie;
        } else {
            throw new Exception\InvalidArgumentException('Supplient argument is not a valid cookie string or object');
        }
    }

    /**
     * Parse an HTTP response, adding all the cookies set in that response
     *
     * @param Uri\Uri|string $refUri Requested URI
     */
    public function addCookiesFromResponse(Response $response, $refUri)
    {
        $cookieHdrs = $response->getHeaders()->get('Set-Cookie');

        if (is_array($cookieHdrs) || $cookieHdrs instanceof ArrayIterator) {
            foreach ($cookieHdrs as $cookie) {
                $this->addCookie($cookie, $refUri);
            }
        } elseif (is_string($cookieHdrs)) {
            $this->addCookie($cookieHdrs, $refUri);
        }
    }

    /**
     * Get all cookies in the cookie jar as an array
     *
     * @param int $retAs Whether to return cookies as objects of \Laminas\Http\Header\SetCookie or as strings
     * @return array|string
     */
    public function getAllCookies($retAs = self::COOKIE_OBJECT)
    {
        return $this->_flattenCookiesArray($this->cookies, $retAs);
    }

    /**
     * Return an array of all cookies matching a specific request according to the request URI,
     * whether session cookies should be sent or not, and the time to consider as "now" when
     * checking cookie expiry time.
     *
     * @param string|Uri\Uri $uri URI to check against (secure, domain, path)
     * @param bool $matchSessionCookies Whether to send session cookies
     * @param int $retAs Whether to return cookies as objects of \Laminas\Http\Header\Cookie or as strings
     * @param int $now Override the current time when checking for expiry time
     * @throws Exception\InvalidArgumentException If invalid URI specified.
     * @return array|string
     */
    public function getMatchingCookies(
        $uri,
        $matchSessionCookies = true,
        $retAs = self::COOKIE_OBJECT,
        $now = null
    ) {
        if (is_string($uri)) {
            $uri = Uri\UriFactory::factory($uri, 'http');
        } elseif (! $uri instanceof Uri\Uri) {
            throw new Exception\InvalidArgumentException('Invalid URI string or object passed');
        }

        $host = $uri->getHost();
        if (empty($host)) {
            throw new Exception\InvalidArgumentException('Invalid URI specified; does not contain a host');
        }

        // First, reduce the array of cookies to only those matching domain and path
        $cookies = $this->_matchDomain($host);
        $cookies = $this->_matchPath($cookies, $uri->getPath());
        $cookies = $this->_flattenCookiesArray($cookies, self::COOKIE_OBJECT);

        // Next, run Cookie->match on all cookies to check secure, time and session matching
        $ret = [];
        foreach ($cookies as $cookie) {
            if ($cookie->match($uri, $matchSessionCookies, $now)) {
                $ret[] = $cookie;
            }
        }
        // Now, use self::_flattenCookiesArray again - only to convert to the return format ;)
        $ret = $this->_flattenCookiesArray($ret, $retAs);

        return $ret;
    }

    /**
     * Get a specific cookie according to a URI and name
     *
     * @param Uri\Uri|string $uri The uri (domain and path) to match
     * @param string $cookieName The cookie's name
     * @param int $retAs Whether to return cookies as objects of \Laminas\Http\Header\SetCookie or as strings
     * @throws Exception\InvalidArgumentException If invalid URI specified or invalid $retAs value.
     * @return SetCookie|string
     */
    public function getCookie($uri, $cookieName, $retAs = self::COOKIE_OBJECT)
    {
        if (is_string($uri)) {
            $uri = Uri\UriFactory::factory($uri, 'http');
        } elseif (! $uri instanceof Uri\Uri) {
            throw new Exception\InvalidArgumentException('Invalid URI specified');
        }

        $host = $uri->getHost();
        if (empty($host)) {
            throw new Exception\InvalidArgumentException('Invalid URI specified; host missing');
        }

        // Get correct cookie path
        $path         = $uri->getPath() ?? '';
        $lastSlashPos = strrpos($path, '/') ?: 0;
        $path         = substr($path, 0, $lastSlashPos);
        if (! $path) {
            $path = '/';
        }

        if (isset($this->cookies[$uri->getHost()][$path][$cookieName])) {
            $cookie = $this->cookies[$uri->getHost()][$path][$cookieName];

            switch ($retAs) {
                case self::COOKIE_OBJECT:
                    return $cookie;

                case self::COOKIE_STRING_ARRAY:
                case self::COOKIE_STRING_CONCAT:
                    return $cookie->__toString();

                default:
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Invalid value passed for $retAs: %s',
                        $retAs
                    ));
            }
        }

        return false;
    }

    /**
     * Helper function to recursively flatten an array. Should be used when exporting the
     * cookies array (or parts of it)
     *
     * @param SetCookie|array $ptr
     * @param int $retAs What value to return
     * @return array|string
     */
    // @codingStandardsIgnoreStart
    protected function _flattenCookiesArray($ptr, $retAs = self::COOKIE_OBJECT)
    {
        // @codingStandardsIgnoreEnd
        if (is_array($ptr)) {
            $ret = $retAs === self::COOKIE_STRING_CONCAT ? '' : [];
            foreach ($ptr as $item) {
                if ($retAs === self::COOKIE_STRING_CONCAT) {
                    $ret .= $this->_flattenCookiesArray($item, $retAs);
                } else {
                    $ret = array_merge($ret, $this->_flattenCookiesArray($item, $retAs));
                }
            }
            return $ret;
        } elseif ($ptr instanceof SetCookie) {
            switch ($retAs) {
                case self::COOKIE_STRING_ARRAY:
                    return [$ptr->__toString()];

                case self::COOKIE_STRING_CONCAT:
                    return $ptr->__toString();

                case self::COOKIE_OBJECT:
                default:
                    return [$ptr];
            }
        }
    }

    /**
     * Return a subset of the cookies array matching a specific domain
     *
     * @param string $domain
     * @return array
     */
    // @codingStandardsIgnoreStart
    protected function _matchDomain($domain)
    {
        // @codingStandardsIgnoreEnd
        $ret = [];

        foreach (array_keys($this->cookies) as $cdom) {
            if (SetCookie::matchCookieDomain($cdom, $domain)) {
                $ret[$cdom] = $this->cookies[$cdom];
            }
        }

        return $ret;
    }

    /**
     * Return a subset of a domain-matching cookies that also match a specified path
     *
     * @param array $domains
     * @param string $path
     * @return array
     */
    // @codingStandardsIgnoreStart
    protected function _matchPath($domains, $path)
    {
        // @codingStandardsIgnoreEnd
        $ret = [];

        foreach ($domains as $dom => $pathsArray) {
            foreach (array_keys($pathsArray) as $cpath) {
                if (SetCookie::matchCookiePath($cpath, $path)) {
                    if (! isset($ret[$dom])) {
                        $ret[$dom] = [];
                    }

                    $ret[$dom][$cpath] = $pathsArray[$cpath];
                }
            }
        }

        return $ret;
    }

    /**
     * Create a new Cookies object and automatically load into it all the
     * cookies set in a Response object. If $uri is set, it will be
     * considered as the requested URI for setting default domain and path
     * of the cookie.
     *
     * @param Response $response HTTP Response object
     * @param Uri\Uri|string $refUri The requested URI
     * @return static
     * @todo Add the $uri functionality.
     */
    public static function fromResponse(Response $response, $refUri)
    {
        $jar = new static();
        $jar->addCookiesFromResponse($response, $refUri);
        return $jar;
    }

    /**
     * Tells if the array of cookies is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this) === 0;
    }

    /**
     * Empties the cookieJar of any cookie
     *
     * @return $this
     */
    public function reset()
    {
        $this->cookies = $this->rawCookies = [];
        return $this;
    }
}
