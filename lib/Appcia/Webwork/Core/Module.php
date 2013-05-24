<?

namespace Appcia\Webwork\Core;

abstract class Module
{
    /**
     * Application
     *
     * @var App
     */
    private $app;

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * Namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Relative path
     *
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param App    $app       Application
     * @param string $name      Name
     * @param string $namespace Namespace
     * @param string $path      Path
     */
    public function __construct(App $app, $name, $namespace, $path)
    {
        $this->app = $app;
        $this->name = (string) $name;
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Register in autoloader
     *
     * @return Module
     */
    public function autoload()
    {
        $path = !empty($this->path) ? $this->path . '/lib' : 'lib';

        $this->app->getAutoloader()
            ->add($this->namespace, $path);

        return $this;
    }

    /**
     * Get application
     *
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Initialize, do only light-weight things like adding configuration, routing etc.
     * Executed by all modules
     *
     * @return Module
     */
    public function init()
    {
        return $this;
    }

    /**
     * Run, prepare heavy things
     * Executed only by target module (according to current route)
     *
     * @return Module
     */
    public function run()
    {
        return $this;
    }
}