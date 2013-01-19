<?

namespace Appcia\Webwork;

class Request
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $scriptFile;

    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $data;

    /**
     * @var [type]
     */
    private $params;

    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $get;

    /**
     * @var string
     */
    private $ip;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = array();
        $this->params = array();
        $this->post = array();
        $this->get = array();
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
     * Set port
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
     * Get port
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
     */
    public function setProtocol($protocol)
    {
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
     * Get protocol url prefix
     *
     * @return mixed
     */
    public function getProtocolPrefix()
    {
        return str_replace(array(
            'HTTP/1.0',
            'HTTP/1.1',
            'HTTPS',
        ), array(
            'http://',
            'http://',
            'https://'
        ), $this->protocol);
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
        $path = $this->getUri();

        if (strpos($path, $this->scriptFile) === 0) {
            $path = substr($path, strlen($this->scriptFile));
        }

        $path = parse_url($path, PHP_URL_PATH);

        if (empty($path)) {
            $path = '/';
        }

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
        return $this->getMethod() == 'post';
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
        $this->data = Config::merge($this->data, $params);

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
        $this->data = Config::merge($this->data, $post);

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
        $this->data = Config::merge($this->data, $get);

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
     * Check whether used method is 'get'
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() == 'get';
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
     * Check whether request has specified parameter
     *
     * @param string $key Parameter name
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }
}