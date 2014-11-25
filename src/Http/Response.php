<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Http;

/**
 * HTTP response class
 *
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Response
{

    /**
     * Response codes & messages
     * @var array
     */
    protected static $responseCodes = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

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
     * Instantiate the response object
     *
     * @param  array $config
     * @throws Exception
     * @return Response
     */
    public function __construct(array $config = [])
    {
        $this->code = (isset($config['code'])) ? $config['code'] : 200;

        if (!array_key_exists($this->code, self::$responseCodes)) {
            throw new Exception('The header code '. $this->code . ' is not allowed.');
        }

        $this->body    = (isset($config['body']))    ? $config['body']    : null;
        $this->message = (isset($config['message'])) ? $config['message'] : self::$responseCodes[$this->code];
        $this->version = (isset($config['version'])) ? $config['version'] : '1.1';

        $this->headers = (isset($config['headers']) && is_array($config['headers'])) ?
            $config['headers'] : ['Content-Type' => 'text/html'];
    }

    /**
     * Parse a response and create a new response object,
     * either from a URL or a full response string
     *
     * @param  string $response
     * @param  array  $context
     * @param  string $mode
     * @throws Exception
     * @return Response
     */
    public static function parse($response, array $context = null, $mode = 'r')
    {
        $headers = [];

        // If a URL, use a stream to get the header and URL contents
        if ((strtolower(substr($response, 0, 7)) == 'http://') || (strtolower(substr($response, 0, 8)) == 'https://')) {
            $client  = new Client\Stream($response, $context, $mode);
            $code    = $client->getCode();
            $headers = $client->getHeaders();
            $body    = $client->getBody();
            $message = $client->getMessage();
            $version = $client->getHttpVersion();
        // Else, if a response string, parse the headers and contents
        } else if (substr($response, 0, 5) == 'HTTP/'){
            if (strpos($response, "\r") !== false) {
                $headerStr = substr($response, 0, strpos($response, "\r\n"));
                $bodyStr = substr($response, (strpos($response, "\r\n") + 2));
            } else {
                $headerStr = substr($response, 0, strpos($response, "\n\n"));
                $bodyStr = substr($response, (strpos($response, "\n\n") + 2));
            }

            $firstLine = trim(substr($headerStr, 0, strpos($headerStr, "\n")));
            $allHeaders = trim(substr($headerStr, strpos($headerStr, "\n")));
            $allHeadersAry = explode("\n", $allHeaders);

            // Get the version, code and message
            $version = substr($firstLine, 0, strpos($firstLine, ' '));
            $version = substr($version, (strpos($version, '/') + 1));
            preg_match('/\d\d\d/', trim($firstLine), $match);
            $code = $match[0];
            $message = str_replace('HTTP/' . $version . ' ' . $code . ' ', '', $firstLine);

            // Get the headers
            foreach ($allHeadersAry as $hdr) {
                $name = substr($hdr, 0, strpos($hdr, ':'));
                $value = substr($hdr, (strpos($hdr, ' ') + 1));
                $headers[trim($name)] = trim($value);
            }

            // If the body content is encoded, decode the body content
            if (array_key_exists('Content-Encoding', $headers)) {
                if (isset($headers['Transfer-Encoding']) && ($headers['Transfer-Encoding'] == 'chunked')) {
                    $bodyStr = self::decodeChunkedBody($bodyStr);
                }
                $body = self::decodeBody($bodyStr, $headers['Content-Encoding']);
            } else {
                $body = $bodyStr;
            }
        } else {
            throw new Exception('The response was not properly formatted.');
        }

        return new Response([
            'code'    => $code,
            'headers' => $headers,
            'body'    => $body,
            'message' => $message,
            'version' => $version
        ]);
    }

    /**
     * Send redirect
     *
     * @param  string $url
     * @param  string $code
     * @param  string $version
     * @throws Exception
     * @return void
     */
    public static function redirect($url, $code = '302', $version = '1.1')
    {
        if (headers_sent()) {
            throw new Exception('The headers have already been sent.');
        }

        if (!array_key_exists($code, self::$responseCodes)) {
            throw new Exception('The header code '. $code . ' is not allowed.');
        }

        header("HTTP/{$version} {$code} " . self::$responseCodes[$code]);
        header("Location: {$url}");
    }

    /**
     * Get response message from code
     *
     * @param  int $code
     * @throws Exception
     * @return string
     */
    public static function getMessageFromCode($code)
    {
        if (!array_key_exists($code, self::$responseCodes)) {
            throw new Exception('The header code ' . $code . ' is not valid.');
        }

        return self::$responseCodes[$code];
    }

    /**
     * Encode the body data
     *
     * @param  string $body
     * @param  string $encode
     * @throws Exception
     * @return string
     */
    public static function encodeBody($body, $encode = 'gzip')
    {
        switch ($encode) {
            // GZIP compression
            case 'gzip':
                if (!function_exists('gzencode')) {
                    throw new Exception('Gzip compression is not available.');
                }
                $encodedBody = gzencode($body);
                break;

            // Deflate compression
            case 'deflate':
                if (!function_exists('gzdeflate')) {
                    throw new Exception('Deflate compression is not available.');
                }
                $encodedBody = gzdeflate($body);
                break;

            // Unknown compression
            default:
                $encodedBody = $body;

        }

        return $encodedBody;
    }

    /**
     * Decode the body data
     *
     * @param  string $body
     * @param  string $decode
     * @throws Exception
     * @return string
     */
    public static function decodeBody($body, $decode = 'gzip')
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
    public static function decodeChunkedBody($body)
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

    /**
     * Determine if the response is successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        $type = floor($this->code / 100);
        return (($type == 3) || ($type == 2) || ($type == 1));
    }

    /**
     * Determine if the response is a redirect
     *
     * @return boolean
     */
    public function isRedirect()
    {
        $type = floor($this->code / 100);
        return ($type == 3);
    }

    /**
     * Determine if the response is an error
     *
     * @return boolean
     */
    public function isError()
    {
        $type = floor($this->code / 100);
        return (($type == 5) || ($type == 4));
    }

    /**
     * Get the response code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the response message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the response header
     *
     * @param  string $name
     * @return string
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get the response headers as a string
     *
     * @param  boolean $status
     * @param  string  $br
     * @return string
     */
    public function getHeadersAsString($status = true, $br = "\n")
    {
        $headers = '';

        if ($status) {
            $headers = "HTTP/{$this->version} {$this->code} {$this->message}{$br}";
        }

        foreach ($this->headers as $name => $value) {
            $headers .= "{$name}: {$value}{$br}";
        }

        return $headers;
    }

    /**
     * Set the response code
     *
     * @param  int $code
     * @throws Exception
     * @return Response
     */
    public function setCode($code)
    {
        if (!array_key_exists($code, self::$responseCodes)) {
            throw new Exception('That header code ' . $code . ' is not allowed.');
        }

        $this->code = $code;
        $this->message = self::$responseCodes[$code];

        return $this;
    }

    /**
     * Set the response message
     *
     * @param  string $message
     * @return Response
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the response body
     *
     * @param  string $body
     * @return Response
     */
    public function setBody($body = null)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set a response header
     *
     * @param  string $name
     * @param  string $value
     * @throws Exception
     * @return Response
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set response headers
     *
     * @param  array $headers
     * @throws Exception
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Set SSL headers to fix file cache issues over SSL in certain browsers.
     *
     * @return Response
     */
    public function setSslHeaders()
    {
        $this->headers['Expires']       = 0;
        $this->headers['Cache-Control'] = 'private, must-revalidate';
        $this->headers['Pragma']        = 'cache';

        return $this;
    }

    /**
     * Send headers
     *
     * @throws Exception
     * @return void
     */
    public function sendHeaders()
    {
        header("HTTP/{$this->version} {$this->code} {$this->message}");
        foreach ($this->headers as $name => $value) {
            header($name . ": " . $value);
        }
    }

    /**
     * Send response
     *
     * @throws Exception
     * @return void
     */
    public function send()
    {
        if (headers_sent()) {
            throw new Exception('The headers have already been sent.');
        }

        $body = $this->body;

        if (array_key_exists('Content-Encoding', $this->headers)) {
            $body = self::encodeBody($body, $this->headers['Content-Encoding']);
            $this->headers['Content-Length'] = strlen($body);
        }

        $this->sendHeaders();
        echo $body;
    }

    /**
     * Return entire response as a string
     *
     * @return string
     */
    public function __toString()
    {
        $body = $this->body;

        if (array_key_exists('Content-Encoding', $this->headers)) {
            $body = self::encodeBody($body, $this->headers['Content-Encoding']);
            $this->headers['Content-Length'] = strlen($body);
        }

        return $this->getHeadersAsString() . "\n" . $body;
    }

}
