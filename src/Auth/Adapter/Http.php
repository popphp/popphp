<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;


/**
 * File auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Http implements AdapterInterface
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
     * Constructor
     *
     * Instantiate the Http auth adapter object
     *
     * @param string $uri
     * @param string $method
     * @throws Exception
     * @return \Pop\Auth\Adapter\Http
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
     * Method to get the auth type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Method to get the auth scheme
     *
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Method to authenticate the user
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate($username, $password)
    {
        $this->generateRequest();

        $context = [
            'http' => [
                'method' => $this->method,
                'header' => null
            ]
        ];

        switch ($this->type) {
            case 'basic':
                $context['http']['header'] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
                break;

            case 'digest':
                $a1 = md5($username . ':' . $this->scheme['realm'] . ':' . $password);
                $a2 = md5($this->method . ':' . $this->relativeUri);
                $r  = md5($a1 . ':' . $this->scheme['nonce'] . ':' . $a2);
                $context['http']['header'] = 'Authorization: Digest username="' . $username .
                    '", realm="' . $this->scheme['realm'] . '", nonce="' . $this->scheme['nonce'] .
                    '", uri="' . $this->relativeUri . '", response="' . $r . '"';
                break;
        }


        $http_response_header = null;
        $stream = @fopen($this->uri, 'r', false, stream_context_create($context));

        if ($stream != false) {
            $meta = stream_get_meta_data($stream);
            $firstLine = $meta['wrapper_data'][0];
        } else {
            $firstLine = $http_response_header[0];
        }

        // Get the version, code and message
        $version = substr($firstLine, 0, strpos($firstLine, ' '));
        $version = substr($version, (strpos($version, '/') + 1));
        preg_match('/\d\d\d/', trim($firstLine), $match);
        $code    = $match[0];
        $message = str_replace('HTTP/' . $version . ' ' . $code . ' ', '', $firstLine);

        echo $version . ' : ' . $code . ' : ' . $message;
    }

    /**
     * Method to generate the request
     *
     * @return void
     */
    protected function generateRequest()
    {
        $http_response_header = null;
        $headers = [];

        $stream = @fopen($this->uri, 'r');
        if ($stream != false) {
            $meta = stream_get_meta_data($stream);
            unset($meta['wrapper_data'][0]);
            $headersAry = $meta['wrapper_data'];
        } else {
            unset($http_response_header[0]);
            $headersAry = $http_response_header;
        }

        // Get the headers
        foreach ($headersAry as $hdr) {
            $name = substr($hdr, 0, strpos($hdr, ':'));
            $value = substr($hdr, (strpos($hdr, ' ') + 1));
            $headers[strtolower(trim($name))] = trim($value);
        }

        if (isset($headers['www-authenticate'])) {
            $this->parseScheme($headers['www-authenticate']);
        }
    }

    /**
     * Method to parse the scheme
     *
     * @param  string $wwwAuth
     * @return void
     */
    protected function parseScheme($wwwAuth)
    {
        $this->type = strtolower(substr($wwwAuth, 0, strpos($wwwAuth, ' ')));
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

}
