<?php

namespace ProjectHuddle\Vendor\Laminas\Http;

use function is_array;

/**
 * Http static client
 */
class ClientStatic
{
    /** @var Client */
    protected static $client;

    /**
     * Get the static HTTP client
     *
     * @param array|Traversable $options
     * @return Client
     */
    protected static function getStaticClient($options = null)
    {
        if (! isset(static::$client) || $options !== null) {
            static::$client = new Client(null, $options);
        }
        return static::$client;
    }

    /**
     * HTTP GET METHOD (static)
     *
     * @param  string $url
     * @param  array $query
     * @param  array $headers
     * @param  mixed $body
     * @param  array|Traversable $clientOptions
     * @return Response|bool
     */
    public static function get($url, $query = [], $headers = [], $body = null, $clientOptions = null)
    {
        if (empty($url)) {
            return false;
        }

        $request = new Request();
        $request->setUri($url);
        $request->setMethod(Request::METHOD_GET);

        if (! empty($query) && is_array($query)) {
            $request->getQuery()->fromArray($query);
        }

        if (! empty($headers) && is_array($headers)) {
            $request->getHeaders()->addHeaders($headers);
        }

        if (! empty($body)) {
            $request->setContent($body);
        }

        return static::getStaticClient($clientOptions)->send($request);
    }

    /**
     * HTTP POST METHOD (static)
     *
     * @param  string $url
     * @param  array $params
     * @param  array $headers
     * @param  mixed $body
     * @param  array|Traversable $clientOptions
     * @throws Exception\InvalidArgumentException
     * @return Response|bool
     */
    public static function post($url, $params, $headers = [], $body = null, $clientOptions = null)
    {
        if (empty($url)) {
            return false;
        }

        $request = new Request();
        $request->setUri($url);
        $request->setMethod(Request::METHOD_POST);

        if (! empty($params) && is_array($params)) {
            $request->getPost()->fromArray($params);
        } else {
            throw new Exception\InvalidArgumentException('The array of post parameters is empty');
        }

        if (! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = Client::ENC_URLENCODED;
        }

        if (! empty($headers) && is_array($headers)) {
            $request->getHeaders()->addHeaders($headers);
        }

        if (! empty($body)) {
            $request->setContent($body);
        }

        return static::getStaticClient($clientOptions)->send($request);
    }
}
