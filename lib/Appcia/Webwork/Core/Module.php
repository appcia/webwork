<?

namespace Appcia\Webwork;

abstract class Module
{
    /**
     * Container
     *
     * @var Container
     */
    protected $container;

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
     * @param Container $container Container
     * @param string    $name      Name
     * @param string    $namespace Namespace
     * @param string    $path      Path
     *
     * @throws Exception
     */
    public function __construct(Container $container, $name, $namespace, $path)
    {
        $this->container = $container;
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

        $bootstrap = $this->container
            ->get('bootstrap');

        $bootstrap->getAutoloader()
            ->add($this->namespace, $path);

        return $this;
    }

    /**
     * Get module specific container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
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