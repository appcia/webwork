<?

namespace Appcia\Webwork\Web;

/**
 * Web request representation
 *
 * @package Appcia\Webwork\Web
 */
class Request
{
    /**
     * Predefined hostname
     */
    const LOCALHOST = '127.0.0.1';

    /**
     * Methods
     */
    const POST = 'post';
    const GET = 'get';

    /**
     * Protocols
     */
    const HTTP_10 = 'HTTP/1.0';
    const HTTP_11 = 'HTTP/1.1';
    const HTTPS = 'HTTPS';

    /**
     * @var array
     */
    protected static $methods = array(
        self::POST,
        self::GET
    );

    /**
     * Protocol names with prefixes
     *
     * @var array
     */
    protected static $protocols = array(
        self::HTTP_10 => 'http://',
        self::HTTP_11 => 'http://',
        self::HTTPS => 'https://'
    );

    /**
     * Source URI
     *
     * @var string
     */
    protected $uri;

    /**
     * Path parsed from URI
     *
     * @var string
     */
    protected $path;

    /**
     * Protocol name
     *
     * @var string
     */
    protected $protocol;

    /**
     * Script file name
     *
     * @var string
     */
    protected $scriptFile;

    /**
     * Server name
     *
     * @var string
     */
    protected $server;

    /**
     * Port number
     *
     * @var string
     */
    protected $port;

    /**
     * Method
     *
     * @var string
     */
    protected $method;

    /**
     * Cumulative data from all methods
     *
     * @var array
     */
    protected $data;

    /**
     * Data passed by route parameters
     *
     * @var [type]
     */
    protected $params;

    /**
     * Data passed by POST method
     *
     * @var array
     */
    protected $post;

    /**
     * Uploaded files data
     *
     * @var array
     */
    protected $files;

    /**
     * Data passed by GET method
     *
     * @var array
     */
    protected $get;

    /**
     * Client IP address
     *
     * @var string
     */
    protected $ip;

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
     * Get known protocol list
     *
     * @return array
     */
    public static function getProtocols()
    {
        return self::$protocols;
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
     * Load request data from global tables
     *
     * @return $this
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
     * Get port number
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set port number
     *
     * @param string $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = (int) $port;

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
     * Set server name
     *
     * @param string $server
     *
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = (string) $server;

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
     * Set executed script filename
     *
     * @param $file
     *
     * @return $this
     */
    public function setScriptFile($file)
    {
        $this->scriptFile = (string) $file;

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
     * Check whether source host is local machine
     *
     * @return boolean
     */
    public function isLocal()
    {
        $flag = ($this->ip === self::LOCALHOST);

        return $flag;
    }

    /**
     * Set client IP
     *
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = (string) $ip;

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
     * Set protocol (http, https)
     *
     * @param $protocol
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setProtocol($protocol)
    {
        if (!isset(self::$protocols[$protocol])) {
            throw new \InvalidArgumentException(sprintf("Unrecognized request protocol: '%s'", $protocol));
        }

        $this->protocol = (string) $protocol;

        return $this;
    }

    /**
     * Get protocol url prefix
     *
     * @return string
     */
    public function getProtocolPrefix()
    {
        return self::$protocols[$this->protocol];
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
     * Get current URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set URI
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = (string) $uri;
        $this->parsePath();

        return $this;
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
     * Get all data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set all data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Check whether used method is 'post'
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() == self::POST;
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
     * Set method
     *
     * @param $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = mb_strtolower($method);

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
     * Set parameters
     *
     * @param array $params Parameters
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        $this->data = array_merge($this->data, $params);

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
     * Set data provided by 'post' method
     *
     * @param array $post Data
     *
     * @return $this
     */
    public function setPost(array $post)
    {
        $this->post = $post;
        $this->data = array_merge($this->data, $post);

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
     * Set data provided by 'get' method
     *
     * @param array $get Data
     *
     * @return $this
     */
    public function setGet(array $get)
    {
        $this->get = $get;
        $this->data = array_merge($this->data, $get);

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
     * Set uploaded files data
     *
     * @param array $files
     *
     * @return $this
     */
    public function setFiles($files)
    {
        $this->files = $files;
        $this->data = array_merge($this->data, $files);

        return $this;
    }

    /**
     * Check whether used method is 'get'
     *
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() == self::GET;
    }

    /**
     * Get request parameter
     *
     * @param string $key Parameter name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf("Request parameter '%s' is not specified.", $key));
        }

        return $this->data[$key];
    }

    /**
     * Grab request parameter
     * If it is not specified returns null
     *
     * @param string $key     Parameter name
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function grab($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * Check whether has specified parameter
     *
     * @param string $key Parameter name
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Check whether used method is ajax (asynchronous)
     *
     * @return boolean
     */
    public function isAjax()
    {
        $header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
        $ajax = ($header == 'xmlhttprequest');

        return $ajax;
    }

    /**
     * Parse URI and create path that could be matched to route
     *
     * @return $this
     */
    protected function parsePath()
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
}