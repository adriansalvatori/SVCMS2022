<?php

namespace ProjectHuddle\Vendor\Laminas\Http\Client\Adapter;

use ProjectHuddle\Vendor\Laminas\Http\Client\Adapter\AdapterInterface as HttpAdapter;
use ProjectHuddle\Vendor\Laminas\Http\Client\Adapter\Exception as AdapterException;
use ProjectHuddle\Vendor\Laminas\Stdlib\ArrayUtils;
use ProjectHuddle\Vendor\Laminas\Uri\Uri;
use Traversable;

use function array_key_exists;
use function base64_decode;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function defined;
use function extension_loaded;
use function gettype;
use function in_array;
use function intval;
use function is_array;
use function is_float;
use function is_numeric;
use function is_resource;
use function number_format;
use function preg_match;
use function preg_replace;
use function preg_split;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use function substr;
use function substr_replace;

use const CURL_HTTP_VERSION_1_0;
use const CURL_HTTP_VERSION_1_1;
use const CURLAUTH_BASIC;
use const CURLINFO_HEADER_OUT;
use const CURLINFO_HEADER_SIZE;
use const CURLOPT_CAINFO;
use const CURLOPT_CAPATH;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_CONNECTTIMEOUT_MS;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_ENCODING;
use const CURLOPT_FILE;
use const CURLOPT_HEADER;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HTTP_VERSION;
use const CURLOPT_HTTPAUTH;
use const CURLOPT_HTTPGET;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_INFILE;
use const CURLOPT_INFILESIZE;
use const CURLOPT_MAXREDIRS;
use const CURLOPT_NOBODY;
use const CURLOPT_PORT;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_PROXY;
use const CURLOPT_PROXYPORT;
use const CURLOPT_PROXYUSERPWD;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_SSLCERT;
use const CURLOPT_SSLCERTPASSWD;
use const CURLOPT_TIMEOUT;
use const CURLOPT_TIMEOUT_MS;
use const CURLOPT_UPLOAD;
use const CURLOPT_URL;
use const CURLOPT_USERPWD;

/**
 * An adapter class for Laminas\Http\Client based on the curl extension.
 * Curl requires libcurl. See for full requirements the PHP manual: http://php.net/curl
 */
class Curl implements HttpAdapter, StreamInterface
{
    /**
     * Operation timeout.
     *
     * @var int
     */
    public const ERROR_OPERATION_TIMEDOUT = 28;

    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = [];

    /**
     * What host/port are we connected to?
     *
     * @var array
     */
    protected $connectedTo = [null, null];

    /**
     * The curl session handle
     *
     * @var resource|null
     */
    protected $curl;

    /**
     * List of cURL options that should never be overwritten
     *
     * @var array
     */
    protected $invalidOverwritableCurlOptions;

    /**
     * Response gotten from server
     *
     * @var string
     */
    protected $response;

    /**
     * Stream for storing output
     *
     * @var resource
     */
    protected $outputStream;

    /**
     * Adapter constructor
     *
     * Config is set using setOptions()
     *
     * @throws AdapterException\InitializationException
     */
    public function __construct()
    {
        if (! extension_loaded('curl')) {
            throw new AdapterException\InitializationException(
                'cURL extension has to be loaded to use ProjectHuddle\Vendor\this Laminas\Http\Client adapter'
            );
        }
        $this->invalidOverwritableCurlOptions = [
            CURLOPT_HTTPGET,
            CURLOPT_POST,
            CURLOPT_UPLOAD,
            CURLOPT_CUSTOMREQUEST,
            CURLOPT_HEADER,
            CURLOPT_RETURNTRANSFER,
            CURLOPT_HTTPHEADER,
            CURLOPT_INFILE,
            CURLOPT_INFILESIZE,
            CURLOPT_PORT,
            CURLOPT_MAXREDIRS,
            CURLOPT_CONNECTTIMEOUT,
        ];
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param  array|Traversable $options
     * @return $this
     * @throws AdapterException\InvalidArgumentException
     */
    public function setOptions($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (! is_array($options)) {
            throw new AdapterException\InvalidArgumentException(sprintf(
                'Array or Traversable object expected, got %s',
                gettype($options)
            ));
        }

        /** Config Key Normalization */
        foreach ($options as $k => $v) {
            unset($options[$k]); // unset original value
            $options[str_replace(['-', '_', ' ', '.'], '', strtolower($k))] = $v; // replace w/ normalized
        }

        if (isset($options['proxyuser']) && isset($options['proxypass'])) {
            $this->setCurlOption(CURLOPT_PROXYUSERPWD, $options['proxyuser'] . ':' . $options['proxypass']);
            unset($options['proxyuser'], $options['proxypass']);
        }

        if (isset($options['sslverifypeer'])) {
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $options['sslverifypeer']);
            unset($options['sslverifypeer']);
        }

        foreach ($options as $k => $v) {
            $option = strtolower($k);
            switch ($option) {
                case 'proxyhost':
                    $this->setCurlOption(CURLOPT_PROXY, $v);
                    break;
                case 'proxyport':
                    $this->setCurlOption(CURLOPT_PROXYPORT, $v);
                    break;
                default:
                    if (is_array($v) && isset($this->config[$option]) && is_array($this->config[$option])) {
                        $v = ArrayUtils::merge($this->config[$option], $v, true);
                    }
                    $this->config[$option] = $v;
                    break;
            }
        }

        return $this;
    }

    /**
     * Retrieve the array of all configuration options
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Direct setter for cURL adapter related options.
     *
     * @param  string|int $option
     * @param  mixed $value
     * @return $this
     */
    public function setCurlOption($option, $value)
    {
        if (! isset($this->config['curloptions'])) {
            $this->config['curloptions'] = [];
        }
        $this->config['curloptions'][$option] = $value;
        return $this;
    }

    /**
     * Initialize curl
     *
     * @param  string  $host
     * @param  int     $port
     * @param  bool $secure
     * @return void
     * @throws AdapterException\RuntimeException If unable to connect.
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If we're already connected, disconnect first
        if ($this->curl) {
            $this->close();
        }

        // Do the actual connection
        $this->curl = curl_init();
        if ($port !== 80) {
            curl_setopt($this->curl, CURLOPT_PORT, intval($port));
        }

        if (isset($this->config['connecttimeout'])) {
            $connectTimeout = $this->config['connecttimeout'];
        } elseif (isset($this->config['timeout'])) {
            $connectTimeout = $this->config['timeout'];
        } else {
            $connectTimeout = null;
        }

        if ($connectTimeout !== null && ! is_numeric($connectTimeout)) {
            throw new AdapterException\InvalidArgumentException(sprintf(
                'integer or numeric string expected, got %s',
                gettype($connectTimeout)
            ));
        }

        if ($connectTimeout !== null) {
            $connectTimeout = (int) $connectTimeout;
        }

        if ($connectTimeout !== null) {
            if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
                curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeout * 1000);
            } else {
                curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            }
        }

        if (isset($this->config['timeout'])) {
            if (defined('CURLOPT_TIMEOUT_MS')) {
                curl_setopt($this->curl, CURLOPT_TIMEOUT_MS, $this->config['timeout'] * 1000);
            } else {
                curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->config['timeout']);
            }
        }

        if (isset($this->config['sslcafile']) && $this->config['sslcafile']) {
            curl_setopt($this->curl, CURLOPT_CAINFO, $this->config['sslcafile']);
        }
        if (isset($this->config['sslcapath']) && $this->config['sslcapath']) {
            curl_setopt($this->curl, CURLOPT_CAPATH, $this->config['sslcapath']);
        }

        if (isset($this->config['maxredirects'])) {
            // Set Max redirects
            curl_setopt($this->curl, CURLOPT_MAXREDIRS, $this->config['maxredirects']);
        }

        if (! $this->curl) {
            $this->close();

            throw new AdapterException\RuntimeException('Unable to Connect to ' . $host . ':' . $port);
        }

        if ($secure !== false) {
            // Behave the same like Laminas\Http\Adapter\Socket on SSL options.
            if (isset($this->config['sslcert'])) {
                curl_setopt($this->curl, CURLOPT_SSLCERT, $this->config['sslcert']);
            }
            if (isset($this->config['sslpassphrase'])) {
                curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD, $this->config['sslpassphrase']);
            }
        }

        // Update connected_to
        $this->connectedTo = [$host, $port];
    }

    /**
     * Send request to the remote server
     *
     * @param  string        $method
     * @param Uri $uri
     * @param  float|string  $httpVersion
     * @param  array         $headers
     * @param  string        $body
     * @return string        $request
     * @throws AdapterException\RuntimeException If connection fails, connected
     *     to wrong host, no PUT file defined, unsupported method, or unsupported
     *     cURL option.
     * @throws AdapterException\InvalidArgumentException If $method is currently not supported.
     * @throws AdapterException\TimeoutException If connection timed out.
     */
    public function write($method, $uri, $httpVersion = '1.1', $headers = [], $body = '')
    {
        if (is_float($httpVersion)) {
            $httpVersion = number_format($httpVersion, 1, '.', '');
        }

        // Make sure we're properly connected
        if (! $this->curl) {
            throw new AdapterException\RuntimeException('Trying to write but we are not connected');
        }

        if ($this->connectedTo[0] !== $uri->getHost() || $this->connectedTo[1] !== $uri->getPort()) {
            throw new AdapterException\RuntimeException('Trying to write but we are connected to the wrong host');
        }

        // set URL
        curl_setopt($this->curl, CURLOPT_URL, $uri->__toString());

        // ensure correct curl call
        $curlValue = true;
        switch ($method) {
            case 'GET':
                $curlMethod = CURLOPT_HTTPGET;
                break;

            case 'POST':
                $curlMethod = CURLOPT_POST;
                break;

            case 'PUT':
                // There are two different types of PUT request, either a Raw Data string has been set
                // or CURLOPT_INFILE and CURLOPT_INFILESIZE are used.
                if (is_resource($body)) {
                    $this->config['curloptions'][CURLOPT_INFILE] = $body;
                }
                if (isset($this->config['curloptions'][CURLOPT_INFILE])) {
                    // Now we will probably already have Content-Length set, so that we have to delete it
                    // from $headers at this point:
                    if (
                        ! isset($headers['Content-Length'])
                        && ! isset($this->config['curloptions'][CURLOPT_INFILESIZE])
                    ) {
                        throw new AdapterException\RuntimeException(
                            'Cannot set a file-handle for cURL option CURLOPT_INFILE'
                            . ' without also setting its size in CURLOPT_INFILESIZE.'
                        );
                    }

                    if (isset($headers['Content-Length'])) {
                        $this->config['curloptions'][CURLOPT_INFILESIZE] = (int) $headers['Content-Length'];
                        unset($headers['Content-Length']);
                    }

                    if (is_resource($body)) {
                        $body = '';
                    }

                    $curlMethod = CURLOPT_UPLOAD;
                } else {
                    $curlMethod = CURLOPT_CUSTOMREQUEST;
                    $curlValue  = 'PUT';
                }
                break;

            case 'PATCH':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue  = 'PATCH';
                break;

            case 'DELETE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue  = 'DELETE';
                break;

            case 'OPTIONS':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue  = 'OPTIONS';
                break;

            case 'TRACE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue  = 'TRACE';
                break;

            case 'HEAD':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue  = 'HEAD';
                break;

            default:
                // For now, through an exception for unsupported request methods
                throw new AdapterException\InvalidArgumentException(sprintf(
                    'Method \'%s\' currently not supported',
                    $method
                ));
        }

        if (is_resource($body) && $curlMethod !== CURLOPT_UPLOAD) {
            throw new AdapterException\RuntimeException('Streaming requests are allowed only with PUT');
        }

        // get http version to use
        $curlHttp = $httpVersion === '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;

        // mark as HTTP request and set HTTP method
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, $curlHttp);
        curl_setopt($this->curl, $curlMethod, $curlValue);

        // Set the CURLOPT_NOBODY flag for HEAD HTTP method
        curl_setopt($this->curl, CURLOPT_NOBODY, $curlMethod === CURLOPT_CUSTOMREQUEST && $curlValue === 'HEAD');

        // Set the CURLINFO_HEADER_OUT flag so that we can retrieve the full request string later
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);

        if ($this->outputStream) {
            // headers will be read into the response
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, [$this, 'readHeader']);
            // and data will be written into the file
            curl_setopt($this->curl, CURLOPT_FILE, $this->outputStream);
        } else {
            // ensure headers are also returned
            curl_setopt($this->curl, CURLOPT_HEADER, true);

            // ensure actual response is returned
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        }

        // Treating basic auth headers in a special way
        if (array_key_exists('Authorization', $headers) && 'Basic' === substr($headers['Authorization'], 0, 5)) {
            curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->curl, CURLOPT_USERPWD, base64_decode(substr($headers['Authorization'], 6)));
            unset($headers['Authorization']);
        }

        // set additional headers
        if (! isset($headers['Accept'])) {
            $headers['Accept'] = '';
        }
        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curlHeaders);

        /**
         * Make sure POSTFIELDS is set after $curlMethod is set:
         *
         * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
         */
        if ($curlMethod === CURLOPT_UPLOAD) {
            // this covers a PUT by file-handle:
            // Make the setting of this options explicit (rather than setting it through the loop following a bit lower)
            // to group common functionality together.
            curl_setopt($this->curl, CURLOPT_INFILE, $this->config['curloptions'][CURLOPT_INFILE]);
            curl_setopt($this->curl, CURLOPT_INFILESIZE, $this->config['curloptions'][CURLOPT_INFILESIZE]);
            unset($this->config['curloptions'][CURLOPT_INFILE]);
            unset($this->config['curloptions'][CURLOPT_INFILESIZE]);
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], true)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        }

        // set additional curl options
        if (isset($this->config['curloptions'])) {
            foreach ((array) $this->config['curloptions'] as $k => $v) {
                if (! in_array($k, $this->invalidOverwritableCurlOptions)) {
                    if (curl_setopt($this->curl, $k, $v) === false) {
                        throw new AdapterException\RuntimeException(sprintf(
                            'Unknown or erroreous cURL option "%s" set',
                            $k
                        ));
                    }
                }
            }
        }

        $this->response = '';

        // send the request

        $response = curl_exec($this->curl);
        // if we used streaming, headers are already there
        if (! is_resource($this->outputStream)) {
            $this->response = $response;
        }

        $request  = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
        $request .= $body;

        if ($response === false || empty($this->response)) {
            if (curl_errno($this->curl) === static::ERROR_OPERATION_TIMEDOUT) {
                throw new AdapterException\TimeoutException(
                    'Read timed out',
                    AdapterException\TimeoutException::READ_TIMEOUT
                );
            }
            throw new AdapterException\RuntimeException(sprintf(
                'Error in cURL request: %s',
                curl_error($this->curl)
            ));
        }

        // separating header from body because it is dangerous to accidentially replace strings in the body
        $responseHeaderSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $responseHeaders    = substr($this->response, 0, $responseHeaderSize);

        // cURL automatically decodes chunked-messages, this means we have to
        // disallow the Laminas\Http\Response to do it again.
        $responseHeaders = preg_replace("/Transfer-Encoding:\s*chunked\\r\\n/i", '', $responseHeaders);

        // cURL can automatically handle content encoding; prevent double-decoding from occurring
        if (
            isset($this->config['curloptions'][CURLOPT_ENCODING])
            && '' === $this->config['curloptions'][CURLOPT_ENCODING]
        ) {
            $responseHeaders = preg_replace("/Content-Encoding:\s*gzip\\r\\n/i", '', $responseHeaders);
        }

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        $responseHeaders = preg_replace(
            "/HTTP\/1.[01]\s*200\s*Connection\s*established\\r\\n\\r\\n/",
            '',
            $responseHeaders
        );

        // replace old header with new, cleaned up, header
        $this->response = substr_replace($this->response, $responseHeaders, 0, $responseHeaderSize);

        // Eliminate multiple HTTP responses.
        do {
            $parts = preg_split('|(?:\r?\n){2}|m', $this->response, 2);
            $again = false;

            if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                $this->response = $parts[1];
                $again          = true;
            }
        } while ($again);

        return $request;
    }

    /**
     * Return read response from server
     *
     * @return string
     */
    public function read()
    {
        return $this->response;
    }

    /**
     * Close the connection to the server
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl        = null;
        $this->connectedTo = [null, null];
    }

    /**
     * Get cUrl Handle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->curl;
    }

    /**
     * Set output stream for the response
     *
     * @param resource $stream
     * @return $this
     */
    public function setOutputStream($stream)
    {
        $this->outputStream = $stream;
        return $this;
    }

    /**
     * Header reader function for CURL
     *
     * @param resource $curl
     * @param string $header
     * @return int
     */
    public function readHeader($curl, $header)
    {
        $this->response .= $header;
        return strlen($header);
    }
}
