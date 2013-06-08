<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Storage\Config;

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
     * Possible environments
     *
     * @var array
     */
    private static $environments = array(
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
    private $rootPath;

    /**
     * PSR autoloader
     *
     * @var object
     */
    private $autoloader;

    /**
     * Current environment
     *
     * @var string
     */
    private $environment;

    /**
     * Registered modules
     *
     * @var Module[]
     */
    private $modules;

    /**
     * Constructor
     */
    public function __construct(Config $config)
    {
        $container = new Container();
        $container->set('app', $this);

        $config->grab('app')
            ->inject($this);

        $this->container = $container;
        $this->config = $config;
        $this->modules = array();
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
     * Get all possible environments
     *
     * @return array
     */
    public static function getEnvironments()
    {
        return self::$environments;
    }

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
     * Get autoloader
     *
     * @return object
     */
    public function getAutoloader()
    {
        return $this->autoloader;
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
     * @throws \OutOfBoundsException
     */
    public function setEnvironment($env)
    {
        if (!in_array($env, self::$environments)) {
            throw new \OutOfBoundsException(sprintf("Invalid environment: '%s'.", $env));
        }

        $this->environment = $env;

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
            throw new \OutOfBoundsException(sprintf("Module '%s' does not exist", $name));
        }

        return $this->modules[$name];
    }

    /**
     * Load all modules basing on configuration
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function loadModules()
    {
        $config = $this->config->get('app');
        if (empty($config)) {
            throw new \InvalidArgumentException("Configuration for base application module is empty."
            . " Check whether key 'app' really exist in config file.");
        }

        $this->loadModule('app', $config);

        $modules = $this->config->get('modules');
        if (empty($modules)) {
            throw new \InvalidArgumentException("Configuration for modules is empty."
            . " Check whether key 'modules' has at least one module specified.");
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
            throw new \InvalidArgumentException(sprintf("Module '%s' does not have path specified", $name));
        }

        $path = $this->getRootPath();

        if (!empty($config['path'])) {
            $path .= '/' . $config['path'];
        }

        $file = $path . '/module.php';

        if ((@include_once($file)) === false) {
            throw new \ErrorException(sprintf(
                "Cannot include module bootstrap '%s'." . PHP_EOL
                . 'Check whether that file really exists.', $file
            ));
        }

        $className = ucfirst($name)
            . '\\' . ucfirst($name) . 'Module';

        if (!class_exists($className)) {
            throw new \ErrorException(sprintf("Module bootstrap '%s' does not contain class '%s'", $file, $className));
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
}