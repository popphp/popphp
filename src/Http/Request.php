<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Http;

/**
 * HTTP request class
 *
 * @category   Pop
 * @package    Pop_Http
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Request
{

    /**
     * Request URI
     * @var string
     */
    protected $requestUri = null;

    /**
     * Path segments
     * @var array
     */
    protected $path = [];

    /**
     * Base path
     * @var string
     */
    protected $basePath = null;

    /**
     * Document root
     * @var string
     */
    protected $docRoot = null;

    /**
     * Full path
     * @var string
     */
    protected $fullPath = null;

    /**
     * Request filename
     * @var string
     */
    protected $filename = null;

    /**
     * Is the request a real file
     * @var boolean
     */
    protected $isFile = false;

    /**
     * Is the request secure
     * @var boolean
     */
    protected $isSecure = false;

    /**
     * $_GET vars
     * @var array
     */
    protected $get = [];

    /**
     * $_POST vars
     * @var array
     */
    protected $post = [];

    /**
     * PUT method vars
     * @var array
     */
    protected $put = [];

    /**
     * PATCH method vars
     * @var array
     */
    protected $patch = [];

    /**
     * DELETE method vars
     * @var array
     */
    protected $delete = [];

    /**
     * $_COOKIE vars
     * @var array
     */
    protected $cookie = [];

    /**
     * $_SERVER vars
     * @var array
     */
    protected $server = [];

    /**
     * $_ENV vars
     * @var array
     */
    protected $env = [];

    /**
     * Headers
     * @var array
     */
    protected $headers = [];

    /**
     * Raw data
     * @var string
     */
    protected $rawData = null;

    /**
     * Constructor
     *
     * Instantiate the request object.
     *
     * @param  string $uri
     * @param  string $basePath
     * @return Request
     */
    public function __construct($uri = null, $basePath = null)
    {
        $this->setRequestUri($uri, $basePath);

        $this->get    = (isset($_GET))    ? $_GET    : [];
        $this->post   = (isset($_POST))   ? $_POST   : [];
        $this->cookie = (isset($_COOKIE)) ? $_COOKIE : [];
        $this->server = (isset($_SERVER)) ? $_SERVER : [];
        $this->env    = (isset($_ENV))    ? $_ENV    : [];

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->parseData();
        }

        // Get any possible request headers
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == 'HTTP_') {
                    $key = ucfirst(strtolower(str_replace('HTTP_', '', $key)));
                    if (strpos($key, '_') !== false) {
                        $ary = explode('_', $key);
                        foreach ($ary as $k => $v){
                            $ary[$k] = ucfirst(strtolower($v));
                        }
                        $key = implode('-', $ary);
                    }
                    $this->headers[$key] = $value;
                }
            }
        }
    }

    /**
     * Return if the file is an actual file
     *
     * @return boolean
     */
    public function isFile()
    {
        return $this->isFile;
    }

    /**
     * Return whether or not the method is GET
     *
     * @return boolean
     */
    public function isGet()
    {
        return ($this->server['REQUEST_METHOD'] == 'GET');
    }

    /**
     * Return whether or not the method is HEAD
     *
     * @return boolean
     */
    public function isHead()
    {
        return ($this->server['REQUEST_METHOD'] == 'HEAD');
    }

    /**
     * Return whether or not the method is POST
     *
     * @return boolean
     */
    public function isPost()
    {
        return ($this->server['REQUEST_METHOD'] == 'POST');
    }

    /**
     * Return whether or not the method is PUT
     *
     * @return boolean
     */
    public function isPut()
    {
        return ($this->server['REQUEST_METHOD'] == 'PUT');
    }

    /**
     * Return whether or not the method is DELETE
     *
     * @return boolean
     */
    public function isDelete()
    {
        return ($this->server['REQUEST_METHOD'] == 'DELETE');
    }

    /**
     * Return whether or not the method is TRACE
     *
     * @return boolean
     */
    public function isTrace()
    {
        return ($this->server['REQUEST_METHOD'] == 'TRACE');
    }

    /**
     * Return whether or not the method is OPTIONS
     *
     * @return boolean
     */
    public function isOptions()
    {
        return ($this->server['REQUEST_METHOD'] == 'OPTIONS');
    }

    /**
     * Return whether or not the method is CONNECT
     *
     * @return boolean
     */
    public function isConnect()
    {
        return ($this->server['REQUEST_METHOD'] == 'CONNECT');
    }

    /**
     * Return whether or not the method is PATCH
     *
     * @return boolean
     */
    public function isPatch()
    {
        return ($this->server['REQUEST_METHOD'] == 'PATCH');
    }

    /**
     * Return whether or not the request is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->isSecure;
    }

    /**
     * Get the base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get the request URI
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Get the full request URI
     *
     * @return string
     */
    public function getFullRequestUri()
    {
        return $this->basePath . $this->requestUri;
    }

    /**
     * Get a path segment, divided by the forward slash,
     * where $num refers to the array key index, i.e.,
     *    0     1     2
     * /hello/world/page
     *
     * No $num returns the whole path segment as an array,
     * and if the $num is not set, then it returns null.
     *
     * @param  int $num
     * @return string|array
     */
    public function getPath($num = null)
    {
        $path = null;

        if (null !== $num) {
            if (isset($this->path[(int)$num])) {
                $path = $this->path[(int)$num];
            }
        } else {
            $path = $this->path;
        }

        return $path;
    }

    /**
     * Get the doc root
     *
     * @return string
     */
    public function getDocRoot()
    {
        return $this->docRoot;
    }

    /**
     * Get the full path
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get secheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->isSecure) ? 'https' : 'http';
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        $host = $this->server['HTTP_HOST'];
        $name = $this->server['SERVER_NAME'];
        $port = $this->server['SERVER_PORT'];

        $hostname = null;

        if (!empty($host)) {
            $hostname = (($port == 80) || ($port == 443)) ? $host : $host . ':' . $port;
        } else if (!empty($name)) {
            $hostname = (($port == 80) || ($port == 443)) ? $name : $name . ':' . $port;
        }

        return $hostname;
    }

    /**
     * Get client's IP
     *
     * @param  boolean $proxy
     * @return string
     */
    public function getIp($proxy = true)
    {
        $ip = null;

        if ($proxy && isset($this->server['HTTP_CLIENT_IP'])) {
            $ip = $this->server['HTTP_CLIENT_IP'];
        } else if ($proxy && isset($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server['HTTP_X_FORWARDED_FOR'];
        } else if (isset($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Get a value from $_GET, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getQuery($key = null)
    {
        if (null === $key) {
            return $this->get;
        } else {
            return (isset($this->get[$key])) ? $this->get[$key] : null;
        }
    }

    /**
     * Get a value from $_POST, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPost($key = null)
    {
        if (null === $key) {
            return $this->post;
        } else {
            return (isset($this->post[$key])) ? $this->post[$key] : null;
        }
    }

    /**
     * Get a value from PUT query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPut($key = null)
    {
        if (null === $key) {
            return $this->put;
        } else {
            return (isset($this->put[$key])) ? $this->put[$key] : null;
        }
    }

    /**
     * Get a value from PATCH query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getPatch($key = null)
    {
        if (null === $key) {
            return $this->patch;
        } else {
            return (isset($this->patch[$key])) ? $this->patch[$key] : null;
        }
    }

    /**
     * Get a value from DELETE query data, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getDelete($key = null)
    {
        if (null === $key) {
            return $this->delete;
        } else {
            return (isset($this->delete[$key])) ? $this->delete[$key] : null;
        }
    }

    /**
     * Get a value from $_COOKIE, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getCookie($key = null)
    {
        if (null === $key) {
            return $this->cookie;
        } else {
            return (isset($this->cookie[$key])) ? $this->cookie[$key] : null;
        }
    }

    /**
     * Get a value from $_SERVER, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getServer($key = null)
    {
        if (null === $key) {
            return $this->server;
        } else {
            return (isset($this->server[$key])) ? $this->server[$key] : null;
        }
    }

    /**
     * Get a value from $_ENV, or the whole array
     *
     * @param  string $key
     * @return string|array
     */
    public function getEnv($key = null)
    {
        if (null === $key) {
            return $this->env;
        } else {
            return (isset($this->env[$key])) ? $this->env[$key] : null;
        }
    }

    /**
     * Get a value from the request headers
     *
     * @param  string $key
     * @return string
     */
    public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : null;
    }

    /**
     * Get the request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the raw data
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Set the request URI
     *
     * @param  string $uri
     * @param  string $basePath
     * @return Request
     */
    public function setRequestUri($uri = null, $basePath = null)
    {
        if ((null === $uri) && isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        if (!empty($basePath)) {
            if (substr($uri, 0, (strlen($basePath) + 1)) == $basePath . '/') {
                $uri = substr($uri, (strpos($uri, $basePath) + strlen($basePath)));
            } else if (substr($uri, 0, (strlen($basePath) + 1)) == $basePath . '?') {
                $uri = '/' . substr($uri, (strpos($uri, $basePath) + strlen($basePath)));
            }
        }

        if (($uri == '') || ($uri == $basePath)) {
            $uri = '/';
        }

        // Some slash clean up
        $this->docRoot = (isset($_SERVER['DOCUMENT_ROOT'])) ? str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']) : null;
        $dir = str_replace('\\', '/', dirname($this->docRoot . $_SERVER['PHP_SELF']));

        if ($dir != $this->docRoot) {
            $realBasePath = str_replace($this->docRoot, '', $dir);
            if (substr($uri, 0, strlen($realBasePath)) == $realBasePath) {
                $this->requestUri = substr($uri, strlen($realBasePath));
            } else {
                $this->requestUri = $uri;
            }
        } else {
            $this->requestUri = $uri;
        }

        $this->basePath = (null === $basePath) ? str_replace($this->docRoot, '', $dir) : $basePath;
        $this->fullPath = $this->docRoot . $this->basePath;
        if (isset($_SERVER['SERVER_PORT'])) {
            $this->isSecure = ($_SERVER['SERVER_PORT'] == '443') ? true : false;
        }

        if (strpos($this->requestUri, '?') !== false) {
            $this->requestUri = substr($this->requestUri, 0, strpos($this->requestUri, '?'));
        }

        if (file_exists($this->fullPath . $this->requestUri)) {
            $this->isFile   = true;
            $this->filename = str_replace('/', '', $this->requestUri);
        } else {
            $this->isFile   = false;
            $this->filename = null;
        }

        if (($this->requestUri != '/') && (strpos($this->requestUri, '/') !== false)) {
            $uri = (substr($this->requestUri, 0, 1) == '/') ? substr($this->requestUri, 1) : $this->requestUri;
            $this->path = explode('/', $uri);
        }

        return $this;
    }

    /**
     * Set the base path
     *
     * @param  string $path
     * @return Request
     */
    public function setBasePath($path = null)
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Set a value for $_GET
     *
     * @param  string $key
     * @param  string $value
     * @return Request
     */
    public function setQuery($key, $value)
    {
        $this->get[$key] = $value;
        $_GET[$key] = $value;
        return $this;
    }

    /**
     * Set a value for $_POST
     *
     * @param  string $key
     * @param  string $value
     * @return Request
     */
    public function setPost($key, $value)
    {
        $this->post[$key] = $value;
        $_POST[$key] = $value;
        return $this;
    }

    /**
     * Parse query data
     *
     * @return void
     */
    protected function parseData()
    {
        $input = fopen('php://input', 'r');

        $paramData = [];

        while ($data = fread($input, 1024)) {
            $this->rawData .= $data;
        }

        // If the content-type is JSON
        if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'json') !== false)) {
            $paramData = json_decode($this->rawData, true);
        // Else, if the content-type is XML
        } else if (isset($_SERVER['CONTENT_TYPE']) && (stripos($_SERVER['CONTENT_TYPE'], 'xml') !== false)) {
            $matches = [];
            preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $this->rawData, $matches);

            foreach ($matches[0] as $match) {
                $strip = str_replace(
                    ['<![CDATA[', ']]>', '<', '>'],
                    ['', '', '&lt;', '&gt;'],
                    $match
                );
                $this->rawData = str_replace($match, $strip, $this->rawData);
            }
            $paramData = json_decode(json_encode((array)simplexml_load_string($this->rawData)), true);
        // Else, default to a regular URL-encoded string
        } else {
            parse_str($this->rawData, $paramData);
        }

        switch (strtoupper($this->getMethod())) {
            case 'PUT':
                $this->put = $paramData;
                break;

            case 'PATCH':
                $this->patch = $paramData;
                break;

            case 'DELETE':
                $this->delete = $paramData;
                break;
        }
    }

}
