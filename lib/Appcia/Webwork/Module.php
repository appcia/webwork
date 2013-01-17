<?

namespace Appcia\Webwork;

abstract class Module
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param Container $container Container
     * @param string    $name      Name
     * @param array     $config    Configuration
     */
    public function __construct(Container $container, $name, array $config)
    {
        $this->container = $container;
        $this->name = (string) $name;
        $this->namespace = (string) $config['namespace'];
        $this->path = (string) $config['path'];
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
     * Register
     */
    public function register()
    {
        $this->container['autoloader']
            ->add($this->namespace, 'module/' . $this->name . '/lib');
    }

    /**
     * Initialize module (triggered on every times on startup when application is running)
     *
     * @return Module
     */
    abstract public function init();

    /**
     * Setup module (triggered by hand via command line)
     *
     * @return Module
     */
    abstract public function setup();
}