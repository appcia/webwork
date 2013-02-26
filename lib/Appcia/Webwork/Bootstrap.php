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
    private $environment;

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
     * Constructor
     *
     * @param string $env        Environment
     * @param string $rootPath   Root directory
     * @param string $configFile Global configuration
     * @param object $autoloader Autoloader
     */
    public function __construct($env, $rootPath, $configFile, $autoloader)
    {
        $this->environment = $env;
        $this->rootPath =  $rootPath;
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
        $this->loadCore();
        $this->loadModules();

        return $this;
    }

    /**
     * Load core components
     *
     * @return Bootstrap
     */
    private function loadCore()
    {
        $bootstrap = $this;
        $this->container->single('bootstrap', function ($container) use ($bootstrap) {
            return $bootstrap;
        });

        $this->container->single('config', function ($container) use ($bootstrap) {
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
            return new Dispatcher($container);
        });

        return $this;
    }

    /**
     * Load all modules basing on config
     *
     * @return Bootstrap
     * @throws \LogicException
     */
    private function loadModules()
    {
        $config = $this->container->get('config');
        $modules = $config->get('modules');

        if (empty($modules)) {
            throw new \LogicException('None modules specified in config');
        }

        $this->loadModule('app', $config['app']);

        foreach ($modules as $name => $config) {
            $this->loadModule($name, $config);
        }

        return $this;
    }

    /**
     * Load single module
     *
     * @param string $name   Keyword name
     * @param array  $config Native data
     *
     * @return Bootstrap
     * @throws \ErrorException
     */
    private function loadModule($name, array $config)
    {
        $path = $this->rootPath . '/' . $config['path'];
        $file = $path . '/module.php';

        if (!file_exists($file)) {
            throw new \ErrorException(sprintf("Cannot find module file '%s'", $file));
        }

        if ((@include_once($file)) === false) {
            throw new \ErrorException(sprintf("Cannot include module bootstrap'%s'", $file));
        }

        $className = ucfirst($name)
            . '\\' . ucfirst($name) . 'Module';

        if (!class_exists($className)) {
            throw new \ErrorException(sprintf("Module file '%s' does not contain class '%s'", $file, $className));
        }

        $module = new $className($this->container, $name, $config);
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
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getModule($name)
    {
        if (!isset($this->modules[$name])) {
            throw new \InvalidArgumentException(sprintf("Module '%s' does not exist", $name));
        }

        return $this->modules[$name];
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
     * Get config file path
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }

    /**
     * Get environment type
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
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