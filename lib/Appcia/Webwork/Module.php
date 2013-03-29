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
     * @param array     $config    Configuration
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container, $name, array $config)
    {
        if (!isset($config['namespace'])) {
            throw new \InvalidArgumentException(sprintf("Module '%s' does not have namespace specified", $name));
        }

        if (!isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf("Module '%s' does not have path specified", $name));
        }

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
     * Initialize, do only necessary / light things like adding configuration, routing etc
     *
     * @return Module
     */
    public function init()
    {
        return $this;
    }

    /**
     * Run, prepare heavy things, routing matched current module
     *
     * @return Module
     */
    public function run()
    {
        return $this;
    }
}