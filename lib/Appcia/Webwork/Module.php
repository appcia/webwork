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
     * Register in autoloader
     */
    public function register()
    {
        $path = !empty($this->path) ? $this->path . '/lib' : 'lib';

        $this->container['autoloader']
            ->add($this->namespace, $path);
    }

    /**
     * Initialize module (triggered every time on startup)
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