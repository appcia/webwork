<?

namespace Appcia\Webwork;

class App
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
     * @param string $rootPath   Root directory
     * @param string $configFile Global configuration
     * @param object $autoloader Autoloader
     */
    public function __construct($rootPath, $configFile, $autoloader)
    {
        $this->container = new Container();

        $this->container['configFile'] = $configFile;
        $this->container['rootPath'] = $rootPath;

        $this->container['app'] = $this;
        $this->container['autoloader'] = $autoloader;

        $this->modules = array();
    }

    /**
     * Init application
     *
     * @return App
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
     * @return App
     */
    private function loadCore()
    {
        $this->container['config'] = $this->container->share(function ($c)  {
            $config = new Config();
            $config->loadFile($c['configFile']);

            return $config;
        });

        $this->container['session'] = $this->container->share(function ($c) {
            $session = new Session();
            $c['config']
                ->get('session')
                ->inject($session);

            $session->loadGlobals();

            return $session;
        });

        $this->container['router'] = $this->container->share(function ($c) {
            $router = new Router();
            $c['config']
                ->get('router')
                ->inject($router);

            return $router;
        });

        $this->container['dispatcher'] = $this->container->share(function ($c) {
            return new Dispatcher($c);
        });

        return $this;
    }

    /**
     * Load modules
     *
     * @return App
     * @throws \LogicException
     */
    private function loadModules()
    {
        if (empty($this->container['config']['modules'])) {
            throw new \LogicException('None modules specified in config');
        }

        $modules = $this->container['config']['modules'];
        foreach ($modules as $name => $config) {
            $path = $this->container['rootPath'] . '/' . trim($config['path'], '/');

            require_once $path . '/module.php';

            $className = ucfirst($name)
                . '\\' . ucfirst($name) . 'Module';

            $module = new $className($this->container, $name, $config);
            $module->register();
            $module->init();

            $this->modules[$name] = $module;
        }

        return $this;
    }

    /**
     * Setup invoked from command line
     *
     * @return App
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
        $this->container['profiler']
            ->start();

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
     * Get registered modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Get module by name
     *
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getModule($name) {
        if (!isset($this->modules[$name])) {
            throw new \InvalidArgumentException(sprintf("Module '%s' does not exist", $name));
        }

        return $this->modules[$name];
    }
}