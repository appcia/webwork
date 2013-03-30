<?

namespace Appcia\Webwork;

class Bootstrap
{
    /**
     * Container
     *
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $modules;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $configFile;

    /**
     * @var object
     */
    private $autoloader;

    /**
     * @var string
     */
    private $environment;

    const DEVELOPMENT = 'development';
    const TEST = 'test';
    const PRODUCTION = 'production';

    /**
     * @var array
     */
    private $environments = array(
        self::DEVELOPMENT,
        self::TEST,
        self::PRODUCTION
    );

    /**
     * Constructor
     *
     * @param string $env        Environment
     * @param string $rootPath   Root directory
     * @param string $configFile Global configuration
     * @param object $autoloader Autoloader
     *
     * @throws Exception
     */
    public function __construct($env, $rootPath, $configFile, $autoloader)
    {
        if (!in_array($env, $this->environments)) {
            throw new Exception('Environment not specified.' . PHP_EOL
                . "Set environmental variable named 'APPLICATION_ENV' in vhost configuration." . PHP_EOL
                . "If you are running CLI, to set this variable, you could use 'export' command.", $env);
        }

        if (!in_array($env, $this->environments)) {
            throw new Exception(sprintf("Invalid environment: '%s'", $env));
        }

        $this->environment = $env;
        $this->rootPath = $rootPath;
        $this->configFile = $configFile;
        $this->autoloader = $autoloader;

        $this->container = new Container();
        $this->modules = array();
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Init application
     *
     * @return Bootstrap
     */
    public function init()
    {
        $this->loadApp();
        $this->loadModules();

        return $this;
    }

    /**
     * Load app module
     *
     * @return Bootstrap
     */
    private function loadApp()
    {
        $this->container->set('bootstrap', $this);

        $this->container->single('config', function ($container) {
            $bootstrap = $container->get('bootstrap');

            $config = new Config();
            $config->loadFile($bootstrap->getConfigFile());

            return $config;
        });

        $this->container->single('session', function ($container) {
            $session = new Session();
            $container->get('config')
                ->get('session')
                ->inject($session);

            $session->loadGlobals();

            return $session;
        });

        $this->container->single('router', function ($container) {
            $router = new Router();
            $container->get('config')
                ->get('router')
                ->inject($router);

            return $router;
        });

        $this->container->single('dispatcher', function ($container) {
            $dispatcher = new Dispatcher($container);
            $container->get('config')
                ->get('dispatcher')
                ->inject($dispatcher);

            return $dispatcher;
        });

        return $this;
    }

    /**
     * Load all modules basing on config
     *
     * @return Bootstrap
     * @throws Exception
     */
    private function loadModules()
    {
        $config = $this->container->get('config');

        if (empty($config['app'])) {
            throw new Exception("Configuration for base application module is empty."
                . " Check whether key 'app' really exist in config file.");
        }

        $this->loadModule('app', $config['app']);

        $modules = $config->get('modules');
        if (empty($modules)) {
            throw new Exception("Configuration for modules is empty."
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
     * @param array $config Native data
     *
     * @return Bootstrap
     * @throws Exception
     */
    private function loadModule($name, array $config)
    {
        if (!isset($config['path'])) {
            throw new Exception(sprintf("Module '%s' does not have path specified", $name));
        }

        $path = $this->rootPath . '/' . $config['path'];
        $file = $path . '/module.php';

        if (!file_exists($file)) {
            throw new Exception(sprintf("Cannot find module bootstrap '%s'", $file));
        }

        if ((@include_once($file)) === false) {
            throw new Exception(sprintf("Cannot include module bootstrap '%s'", $file));
        }

        $className = ucfirst($name)
            . '\\' . ucfirst($name) . 'Module';

        if (!class_exists($className)) {
            throw new Exception(sprintf("Module bootstrap '%s' does not contain class '%s'", $file, $className));
        }

        $module = new $className(
            $this->container,
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
     * Get all loaded modules
     *
     * @return array
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
     * @throws Exception
     */
    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            throw new Exception(sprintf("Module '%s' does not exist", $name));
        }

        return $this->modules[$name];
    }

    /**
     * Get autoloader (provided by composer)
     *
     * @return object
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * Get config file path
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
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
     * Get all possible environments
     *
     * @return array
     */
    public function getEnvironments()
    {
        return $this->environments;
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
}