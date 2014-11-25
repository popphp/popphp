<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;


/**
 * Http auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Http extends AbstractAdapter
{

    /**
     * Auth URI
     * @var string
     */
    protected $uri = null;

    /**
     * Auth relative URI
     * @var string
     */
    protected $relativeUri = null;

    /**
     * Auth method
     * @var string
     */
    protected $method = 'GET';

    /**
     * Auth type
     * @var string
     */
    protected $type = null;

    /**
     * Scheme values
     * @var array
     */
    protected $scheme = [];

    /**
     * HTTP version
     * @var string
     */
    protected $version = '1.1';

    /**
     * Response code
     * @var int
     */
    protected $code = null;

    /**
     * Response message
     * @var string
     */
    protected $message = null;

    /**
     * Response headers
     * @var array
     */
    protected $headers = [];

    /**
     * Response body
     * @var string
     */
    protected $body = null;

    /**
     * Constructor
     *
     * Instantiate the Http auth adapter object
     *
     * @param string $uri
     * @param string $method
     * @throws Exception
     * @return Http
     */
    public function __construct($uri, $method = 'GET')
    {
        if (stripos($uri, 'http') === false) {
            throw new Exception('Error: The URI parameter must be a fully URI with the HTTP scheme.');
        }

        $this->uri         = $uri;
        $this->relativeUri = substr($uri, (strpos($uri, '://') + 3));
        $this->relativeUri = substr($this->relativeUri, strpos($this->relativeUri, '/'));

        $method = strtoupper($method);

        if (($method == 'GET') || ($method == 'POST') || ($method == 'PUT') || ($method == 'PATCH')) {
            $this->method = $method;
        }
    }

    /**
     * Get the auth type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the auth scheme
     *
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the HTTP version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the HTTP code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the HTTP message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the HTTP response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get an HTTP response header
     *
     * @param  string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get the HTTP response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Method to authenticate
     *
     * @return int
     */
    public function authenticate()
    {
        $this->generateRequest();

        $context = [
            'http' => [
                'method' => $this->method,
                'header' => null
            ]
        ];

        switch ($this->type) {
            case 'Basic':
                $context['http']['header'] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
                break;

            case 'Digest':
                $a1 = md5($this->username . ':' . $this->scheme['realm'] . ':' . $this->password);
                $a2 = md5($this->method . ':' . $this->relativeUri);
                $r  = md5($a1 . ':' . $this->scheme['nonce'] . ':' . $a2);
                $context['http']['header'] = 'Authorization: Digest username="' . $this->username .
                    '", realm="' . $this->scheme['realm'] . '", nonce="' . $this->scheme['nonce'] .
                    '", uri="' . $this->relativeUri . '", response="' . $r . '"';
                break;
        }

        $this->sendRequest($context);

        return ($this->code == 200) ? 1 : 0;
    }

    /**
     * Generate the request
     *
     * @return void
     */
    protected function generateRequest()
    {
        $this->sendRequest();

        // Check for the WWW Auth header and parse it
        if (isset($this->headers['WWW-Authenticate'])) {
            $this->parseScheme($this->headers['WWW-Authenticate']);
        } else if (isset($this->headers['WWW-authenticate'])) {
            $this->parseScheme($this->headers['WWW-authenticate']);
        } else if (isset($this->headers['Www-Authenticate'])) {
            $this->parseScheme($this->headers['Www-Authenticate']);
        } else if (isset($this->headers['Www-Authenticate'])) {
            $this->parseScheme($this->headers['www-authenticate']);
        }
    }

    /**
     * Parse the scheme
     *
     * @param  string $wwwAuth
     * @return void
     */
    protected function parseScheme($wwwAuth)
    {
        $this->type = substr($wwwAuth, 0, strpos($wwwAuth, ' '));
        $scheme     = explode(', ', substr($wwwAuth, (strpos($wwwAuth, ' ') + 1)));

        foreach ($scheme as $sch) {
            $sch   = trim($sch);
            $name  = substr($sch,0, strpos($sch, '='));
            $value = substr($sch, (strpos($sch, '=') + 1));
            if ((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) {
                $value = substr($value, 1);
                $value = substr($value, 0, -1);
            }
            $this->scheme[$name] = $value;
        }
    }

    /**
     * Send the request
     *
     * @param  array $context
     * @return void
     */
    protected function sendRequest(array $context = null)
    {
        $http_response_header = null;

        if (null !== $context) {
            $stream = @fopen($this->uri, 'r', false, stream_context_create($context));
        } else {
            $stream = @fopen($this->uri, 'r');
        }

        if ($stream != false) {
            $meta = stream_get_meta_data($stream);
            $firstLine = $meta['wrapper_data'][0];
            unset($meta['wrapper_data'][0]);
            $allHeadersAry = $meta['wrapper_data'];
            $this->body = stream_get_contents($stream);
        } else {
            $firstLine = $http_response_header[0];
            unset($http_response_header[0]);
            $allHeadersAry = $http_response_header;
            $this->body = null;
        }

        // Get the version, code and message
        $this->version = substr($firstLine, 0, strpos($firstLine, ' '));
        $this->version = substr($this->version, (strpos($this->version, '/') + 1));
        preg_match('/\d\d\d/', trim($firstLine), $match);
        $this->code    = $match[0];
        $this->message = str_replace('HTTP/' . $this->version . ' ' . $this->code . ' ', '', $firstLine);

        // Get the headers
        foreach ($allHeadersAry as $hdr) {
            $name = substr($hdr, 0, strpos($hdr, ':'));
            $value = substr($hdr, (strpos($hdr, ' ') + 1));
            $this->headers[trim($name)] = trim($value);
        }

        // If the body content is encoded, decode the body content
        if (array_key_exists('Content-Encoding', $this->headers)) {
            if (isset($headers['Transfer-Encoding']) && ($this->headers['Transfer-Encoding'] == 'chunked')) {
                $this->body = self::decodeChunkedBody($this->body);
            }
            $this->body = self::decodeBody($this->body, $headers['Content-Encoding']);
        }
    }

    /**
     * Decode the body data.
     *
     * @param  string $body
     * @param  string $decode
     * @throws Exception
     * @return string
     */
    protected static function decodeBody($body, $decode = 'gzip')
    {
        switch ($decode) {
            // GZIP compression
            case 'gzip':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Gzip compression is not available.');
                }
                $decodedBody = gzinflate(substr($body, 10));
                break;

            // Deflate compression
            case 'deflate':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Deflate compression is not available.');
                }
                $zlibHeader = unpack('n', substr($body, 0, 2));
                $decodedBody = ($zlibHeader[1] % 31 == 0) ? gzuncompress($body) : gzinflate($body);
                break;

            // Unknown compression
            default:
                $decodedBody = $body;

        }

        return $decodedBody;
    }

    /**
     * Decode a chunked transfer-encoded body and return the decoded text
     *
     * @param string $body
     * @return string
     */
    protected static function decodeChunkedBody($body)
    {
        $decoded = '';

        while($body != '') {
            $lfPos = strpos($body, "\012");

            if ($lfPos === false) {
                $decoded .= $body;
                break;
            }

            $chunkHex = trim(substr($body, 0, $lfPos));
            $scPos    = strpos($chunkHex, ';');

            if ($scPos !== false) {
                $chunkHex = substr($chunkHex, 0, $scPos);
            }

            if ($chunkHex == '') {
                $decoded .= substr($body, 0, $lfPos);
                $body = substr($body, $lfPos + 1);
                continue;
            }

            $chunkLength = hexdec($chunkHex);

            if ($chunkLength) {
                $decoded .= substr($body, $lfPos + 1, $chunkLength);
                $body = substr($body, $lfPos + 2 + $chunkLength);
            } else {
                $body = '';
            }
        }

        return $decoded;
    }

}
