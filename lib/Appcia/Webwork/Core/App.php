<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\System\Dir;
use Appcia\Webwork\Web\Response;

/**
 * Base for modular application
 *
 * @package Appcia\Webwork\Core
 */
abstract class App
{
    const DEVELOPMENT = 'dev';
    const TEST = 'test';
    const PRODUCTION = 'prod';

    /**
     * Available environments
     *
     * @var array
     */
    protected static $environments = array(
        self::DEVELOPMENT,
        self::TEST,
        self::PRODUCTION
    );

    /**
     * DI container
     *
     * @var Container
     */
    protected $container;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Root path
     *
     * @var string
     */
    protected $rootPath;

    /**
     * PSR autoloader
     *
     * @var object
     */
    protected $autoloader;

    /**
     * PHP settings
     *
     * @var array
     */
    protected $php;

    /**
     * Current environment
     *
     * @var string
     */
    protected $environment;

    /**
     * Registered modules
     *
     * @var Module[]
     */
    protected $modules;

    /**
     * Exception handlers
     *
     * @var array
     */
    protected $handlers;

    /**
     * Default error handler
     *
     * @var \Closure|NULL
     */
    protected $errorHandler;

    /**
     * Constructor
     */
    public function __construct(Config $config)
    {
        $this->registerHandler(true);

        $container = new Container();
        $container->set('app', $this);

        $this->container = $container;
        $this->config = $config;

        $this->modules = array();
        $this->environment = self::DEVELOPMENT;

        $config->grab('app')
            ->inject($this);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->registerHandler(false);
    }

    /**
     * Get available environments
     *
     * @return array
     */
    public static function getEnvironments()
    {
        return self::$environments;
    }

    /**
     * Get PHP settings
     *
     * @return array
     */
    public function getPhp()
    {
        return $this->php;
    }

    /**
     * Set PHP settings
     *
     * @param array $settings
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setPhp(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (ini_get($name) === false) {
                throw new \InvalidArgumentException(sprintf(
                    "Setting '%s' is invalid or unsupported in current PHP version (%s).",
                    $name,
                    PHP_VERSION
                ));
            }

            ini_set($name, $value);
        }

        $this->php = $settings;

        return $this;
    }

    /**
     * Run
     *
     * @return $this
     */
    abstract public function run();

    /**
     * Bootstrap
     *
     * @return $this
     */
    abstract public function bootstrap();

    /**
     * Quick get service or parameter from DI container
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->container->get($key);
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Check whether is running via CGI interface
     *
     * @return boolean
     */
    public function isCgi()
    {
        $sapi = $this->getSapi();
        $flag = (substr($sapi, 0, 3) == 'cgi');

        return $flag;
    }

    /**
     * Get server API
     *
     * @return string
     */
    public function getSapi()
    {
        if (defined('PHP_SAPI') && PHP_SAPI !== '') {
            return PHP_SAPI;
        } else {
            return php_sapi_name();
        }
    }

    /**
     * Check whether is running in CLI mode (console)
     *
     * @return boolean
     */
    public function isCli()
    {
        $sapi = $this->getSapi();
        $flag = (substr($sapi, 0, 3) == 'cli');

        return $flag;
    }

    /**
     * Get autoloader
     *
     * @return object
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * Set autoloader (could be from Composer)
     *
     * @param object $autoloader
     *
     * @return $this
     */
    public function setAutoloader($autoloader)
    {
        $this->autoloader = $autoloader;

        return $this;
    }

    /**
     * Get current environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set environment
     *
     * @param string $env Environment
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setEnvironment($env)
    {
        if (empty($env)) {
            throw new \InvalidArgumentException("Application environment cannot be empty.");
        }

        $this->environment = (string) $env;

        return $this;
    }

    /**
     * Get all loaded modules
     *
     * @return Module[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Get loaded module by name
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            throw new \OutOfBoundsException(sprintf("Module '%s' does not exist.", $name));
        }

        return $this->modules[$name];
    }

    /**
     * Find existing sub-paths in all modules
     *
     * @param string  $subPath   Sub path
     * @param boolean $namespace Use namespace as path prefix
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function findPaths($subPath, $namespace = true)
    {
        if (empty($subPath)) {
            throw new \InvalidArgumentException("Module sub-path cannot be empty.");
        }

        $paths = array();

        foreach ($this->modules as $module) {
            $path = $module->getPath();

            if ($namespace) {
                if (empty($path)) {
                    $path = 'lib/' . $module->getNamespace();
                } else {
                    $path .= '/lib/' . $module->getNamespace();
                }
            }

            $path .= '/' . $subPath;
            $dir = new Dir($path);

            if ($dir->exists()) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Load all modules basing on configuration
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function loadModules()
    {
        $modules = $this->config->get('app.module');
        if (empty($modules)) {
            throw new \InvalidArgumentException("Configuration for modules is empty."
            . " Check whether key 'app.module' has at least one module specified.");
        }

        foreach ($modules as $name => $config) {
            $this->loadModule($name, $config);
        }

        return $this;
    }

    /**
     * Load single module
     *
     * @param string $name   Keyword name
     * @param array  $config Config data
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     */
    protected function loadModule($name, array $config)
    {
        if (!isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf("Module '%s' does not have path specified.", $name));
        }

        $path = $this->getRootPath();

        if (!empty($config['path'])) {
            $path .= '/' . $config['path'];
        }

        $file = $path . '/module.php';

        if (!file_exists($file)) {
            throw new \ErrorException(sprintf(
                "Cannot include module bootstrap '%s'." . PHP_EOL
                . 'Check whether that file really exists.', $file
            ));
        }

        include_once $file;

        $className = ucfirst($name)
            . '\\' . ucfirst($name) . 'Module';

        if (!class_exists($className)) {
            throw new \ErrorException(sprintf("Module bootstrap '%s' does not contain class '%s'.", $file, $className));
        }

        $module = new $className(
            $this,
            $name,
            $config['namespace'],
            $config['path']
        );

        $module->autoload()
            ->init();

        $this->modules[$name] = $module;

        return $this;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * Set root path
     *
     * @param string $rootPath Path
     *
     * @return $this
     */
    public function setRootPath($rootPath)
    {
        chdir($rootPath);
        $this->rootPath = $rootPath;

        return $this;
    }

    /**
     * Callback for throwing exception on error
     *
     * @param int    $no      Error number
     * @param string $message Error Message
     * @param string $path    File path
     * @param int    $line    Line number
     *
     * @throws \ErrorException
     */
    public function throwHandler($no, $message, $path, $line)
    {
        throw new \ErrorException($message, $no, 0, $path, $line);
    }

    /**
     * Enable or disable exception triggering on error
     *
     * @param boolean $flag Toggle
     *
     * @return $this
     */
    public function registerHandler($flag)
    {
        if (!$flag && $this->errorHandler) {
            set_error_handler($this->errorHandler);
        }

        if ($flag) {
            $this->errorHandler = set_error_handler(array($this, 'throwHandler'));
        }

        return $this;
    }

    /**
     * Register exception handler
     *
     * Exception could be:
     * - class name for example: Appcia\Webwork\NotFound
     * - object     for example: new Appcia\Webwork\Exception\NotFound()
     * - boolean    if should always / never handle any type of exception
     *
     * @param mixed    $exception Exception to be handled, see description!
     * @param callable $callback  Callback function
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function handle($exception, \Closure $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Handler callback is invalid');
        }

        if (is_object($exception)) {
            if (!$exception instanceof \Exception) {
                throw new \InvalidArgumentException('Invalid exception to be handled');
            }

            $exception = get_class($exception);
        }

        $this->handlers[] = array(
            'exception' => $exception,
            'callback' => $callback
        );

        return $this;
    }

    /**
     * React when exception occurred
     *
     * @param \Exception $e Exception
     *
     * @return Response
     * @throws \Exception
     */
    public function react($e)
    {
        // Look for most detailed exception handler
        $exception = get_class($e);
        $specificHandler = null;
        $allHandler = null;

        foreach ($this->handlers as $handler) {
            if ($handler['exception'] === true) {
                $allHandler = $handler;
            }

            if ($handler['exception'] === $exception) {
                $specificHandler = $handler;
            }
        }

        $handler = $allHandler;
        if ($specificHandler !== null) {
            $handler = $specificHandler;
        }

        if ($handler === null) {
            throw $e;
        }

        // Call for new response
        $response = call_user_func($handler['callback'], $this);

        if (!$response instanceof Response) {
            throw new \ErrorException('Error handler callback should return response object.');
        }

        return $response;
    }
}