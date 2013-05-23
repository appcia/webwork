<?

namespace Appcia\Webwork;

class Request
{
    /**
     * Source URI
     *
     * @var string
     */
    private $uri;

    /**
     * Path parsed from URI
     *
     * @var string
     */
    private $path;

    /**
     * Protocol name
     *
     * @var string
     */
    private $protocol;

    /**
     * Script file name
     *
     * @var string
     */
    private $scriptFile;

    /**
     * Server name
     *
     * @var string
     */
    private $server;

    /**
     * Port number
     *
     * @var string
     */
    private $port;

    const POST = 'post';
    const GET = 'get';

    /**
     * Valid method list
     *
     * @var array
     */
    private static $methods = array(
        self::POST,
        self::GET
    );

    /**
     * Method
     *
     * @var string
     */
    private $method;

    /**
     * Cumulative data from all methods
     *
     * @var array
     */
    private $data;

    /**
     * Data passed by route parameters
     *
     * @var [type]
     */
    private $params;

    /**
     * Data passed by POST method
     *
     * @var array
     */
    private $post;

    /**
     * Uploaded files data
     *
     * @var array
     */
    private $files;

    /**
     * Data passed by GET method
     *
     * @var array
     */
    private $get;

    /**
     * Client IP address
     *
     * @var string
     */
    private $ip;

    const HTTP_10 = 'HTTP/1.0';
    const HTTP_11 = 'HTTP/1.1';
    const HTTPS = 'HTTPS';

    /**
     * Protocol names with prefixes
     *
     * @var array
     */
    private static $protocols = array(
        self::HTTP_10 => 'http://',
        self::HTTP_11 => 'http://',
        self::HTTPS => 'https://'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = array();
        $this->params = array();
        $this->post = array();
        $this->get = array();
        $this->files = array();
    }

    /**
     * Load request data from superglobal tables
     *
     * @return Request
     */
    public function loadGlobals()
    {
        $this->setPost($_POST);
        $this->setGet($_GET);
        $this->setFiles($_FILES);

        $this->setScriptFile($_SERVER['SCRIPT_NAME'])
            ->setServer($_SERVER['SERVER_NAME'])
            ->setMethod($_SERVER['REQUEST_METHOD'])
            ->setProtocol($_SERVER['SERVER_PROTOCOL'])
            ->setPort($_SERVER['SERVER_PORT'])
            ->setIp($_SERVER['REMOTE_ADDR']);

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->setUri($_SERVER['REQUEST_URI']);
        } else {
            $this->setUri($_SERVER['PHP_SELF']);
        }

        return $this;
    }

    /**
     * Set port number
     *
     * @param string $port
     *
     * @return Request
     */
    public function setPort($port)
    {
        $this->port = (int) $port;

        return $this;
    }

    /**
     * Get port number
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set server name
     *
     * @param string $server
     *
     * @return Request
     */
    public function setServer($server)
    {
        $this->server = (string) $server;

        return $this;
    }

    /**
     * Get server name
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set executed script filename
     *
     * @param $file
     *
     * @return Request
     */
    public function setScriptFile($file)
    {
        $this->scriptFile = (string) $file;

        return $this;
    }

    /**
     * Get script file
     *
     * @return string
     */
    public function getScriptFile()
    {
        return $this->scriptFile;
    }

    /**
     * Set client IP
     *
     * @param string $ip
     *
     * @return Request
     */
    public function setIp($ip)
    {
        $this->ip = (string) $ip;

        return $this;
    }

    /**
     * Get client IP
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set protocol (http, https)
     *
     * @param $protocol
     *
     * @return Request
     * @throws Exception
     */
    public function setProtocol($protocol)
    {
        if (!isset(self::$protocols[$protocol])) {
            throw new Exception(sprintf("Unrecognized request protocol: '%s'", $protocol));
        }

        $this->protocol = (string) $protocol;

        return $this;
    }

    /**
     * Get protocol (http, https)
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Get known protocol list
     *
     * @return array
     */
    public static function getProtocols()
    {
        return self::$protocols;
    }

    /**
     * Get protocol url prefix
     *
     * @return string
     * @throws Exception
     */
    public function getProtocolPrefix()
    {
        return  self::$protocols[$this->protocol];
    }

    /**
     * Set URI
     *
     * @param string $uri
     *
     * @return Request
     */
    public function setUri($uri)
    {
        $this->uri = (string) $uri;
        $this->parsePath();

        return $this;
    }

    /**
     * Parse URI and create path that could be matched to route
     *
     * @return Request
     */
    private function parsePath()
    {
        $path = $this->uri;

        if (strpos($path, $this->scriptFile) === 0) {
            $path = substr($path, strlen($this->scriptFile));
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $path = parse_url($path, PHP_URL_PATH);

        $this->path = $path;

        return $this;
    }

    /**
     * Get current path (to be matched for route)
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get parameters that occurs in URI
     *
     * @return array
     */
    public function getUriParams()
    {
        $params = array_merge($this->get, $this->params);

        return $params;
    }

    /**
     * Set method
     *
     * @param $method
     *
     * @return Request
     */
    public function setMethod($method)
    {
        $this->method = mb_strtolower($method);

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get valid method values
     *
     * @return array
     */
    public static function getMethods()
    {
        return self::$methods;
    }

    /**
     * Set data (skipped source method information)
     *
     * @param array $data
     *
     * @return Request
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data (skipped source method information)
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check whether used method is 'post'
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() == self::POST;
    }

    /**
     * Set parameters
     *
     * @param array $params Parameters
     *
     * @return Request
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        $this->data = array_merge($this->data, $params);

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set data provided by 'post' method
     *
     * @param array $post Data
     *
     * @return Request
     */
    public function setPost(array $post)
    {
        $this->post = $post;
        $this->data = array_merge($this->data, $post);

        return $this;
    }

    /**
     * Get data provided by 'post' method
     *
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set data provided by 'get' method
     *
     * @param array $get Data
     *
     * @return Request
     */
    public function setGet(array $get)
    {
        $this->get = $get;
        $this->data = array_merge($this->data, $get);

        return $this;
    }

    /**
     * Get data provided by 'get' method
     *
     * @return array
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * Set uploaded files data
     *
     * @param array $files
     *
     * @return Request
     */
    public function setFiles($files)
    {
        $this->files = $files;
        $this->data = array_merge($this->data, $files);

        return $this;
    }

    /**
     * Get uploaded files data
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Check whether used method is 'get'
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() == self::GET;
    }

    /**
     * Get request parameter (skipped method type information)
     *
     * @param string $key Parameter name
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Check whether has specified parameter
     *
     * @param string $key Parameter name
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Check whether used method is ajax (asynchronous)
     *
     * @return bool
     */
    public function isAjax()
    {
        $header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
        $ajax = ($header == 'xmlhttprequest');

        return $ajax;
    }
}