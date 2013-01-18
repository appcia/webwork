<?

namespace Appcia\Webwork;

class Bootstrap
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $modules;

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
        $this->container = new Container();

        $this->container['environment'] = $env;
        $this->container['rootPath'] = $rootPath;
        $this->container['configFile'] = $configFile;

        $this->container['bootstrap'] = $this;
        $this->container['autoloader'] = $autoloader;

        $this->modules = array();
    }

    /**
     * Get container for external dependencies
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
        $this->container->single('config', function ($c) {
            $config = new Config();
            $config->loadFile($c['configFile']);

            return $config;
        });

        $this->container->single('session', function ($c) {
            $session = new Session();
            $c['config']
                ->get('session')
                ->inject($session);

            $session->loadGlobals();

            return $session;
        });

        $this->container->single('router', function ($c) {
            $router = new Router();
            $c['config']
                ->get('router')
                ->inject($router);

            return $router;
        });

        $this->container->single('dispatcher', function ($c) {
            return new Dispatcher($c);
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
        if (empty($this->container['config']['modules'])) {
            throw new \LogicException('None modules specified in config');
        }

        $this->loadModule('app', $this->container['config']['app']);

        $modules = $this->container['config']['modules'];
        foreach ($modules as $name => $config) {
            $this->loadModule($name, $config);
        }

        return $this;
    }

    /**
     * Load single module
     *
     * @param $name         Keyword name
     * @param array $config Native data
     */
    private function loadModule($name, array $config)
    {
        $path = $this->container['rootPath'] . '/' . trim($config['path'], '/');

        require_once $path . '/module.php';

        $className = ucfirst($name)
            . '\\' . ucfirst($name) . 'Module';

        $module = new $className($this->container, $name, $config);
        $module->register();
        $module->init();

        $this->modules[$name] = $module;
    }

    /**
     * Setup invoked from command line
     *
     * @return Bootstrap
     */
    public function setup()
    {
        foreach ($this->modules as $module) {
            $module->setup();
        }
    }

    /**
     * Run application in browser
     *
     * @return int
     */
    public function run()
    {
        $request = new Request();
        $request->loadGlobals();

        $this->container['request'] = $request;

        $response = $this->container['dispatcher']
            ->setRequest($request)
            ->dispatch()
            ->getResponse();

        $this->container['response'] = $response;

        $response->display();

        return $response->getStatus();
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
}