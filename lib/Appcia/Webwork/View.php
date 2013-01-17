<?

namespace Appcia\Webwork;

use Appcia\Webwork\View\Helper;

/**
 * Views with shared variables, helper mechanism
 */
class View
{
    /**
     * @var array
     */
    private $defaults;

    /**
     * @var array
     */
    private $helpers;

    /**
     * @var array
     */
    private $data;

    /*
     * @var string
     */
    private $file;

    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->defaults = array();
        $this->data = array();
        $this->helpers = array();

        $this->container = $container;
    }

    /**
     * Get app container
     * Can be used only in view created by dispatcher
     *
     * @return Container
     * @throws \InvalidArgumentException
     */
    public function getContainer()
    {
        if ($this->container === null) {
            throw new \InvalidArgumentException('Invalid use. There is no container associated with view');
        }

        return $this->container;
    }

    /**
     * Set default values (e.g for helpers)
     *
     * @param array $defaults Data
     *
     * @return View
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Get default values (e.g for helpers)
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set data
     *
     * @param array $data Data
     *
     * @return View
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Append data
     *
     * @param array $data Data
     *
     * @return View
     */
    public function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Get view data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set view file
     *
     * @param $file
     *
     * @return View
     */
    public function setFile($file)
    {
        $this->file = (string)$file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get generated view content
     *
     * @param string $file File path
     *
     * @return string
     * @throws \ErrorException
     */
    public function render($file = null)
    {
        if (!$file) {
            $file = $this->getFile();
        }

        if (!file_exists($file)) {
            throw new \ErrorException(sprintf("View file not found: '%s'", $file));
        }

        extract($this->data);

        ob_start();

        if ((include $file) !== 1) {
            throw new \ErrorException(sprintf("View file cannot be included properly: '%s'", $file));
        }

        return ob_get_clean();
    }

    /**
     * Get helper by name
     *
     * @param string $name Name
     *
     * @return Helper
     * @throws \InvalidArgumentException
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            $className = 'Appcia\\Webwork\\View\\Helper\\' . ucfirst($name);
            $helper = new $className();
            $helper->setView($this);

            if (!empty($this->defaults['helper']) && !empty($this->defaults['helper'][$name])) {
                $config = new Config($this->defaults['helper'][$name]);
                $config->inject($helper);
            }

            $this->helpers[$name] = $helper;
        }

        return $this->helpers[$name];
    }

    /**
     * Get global default value, for charsets etc, shared within helpers
     *
     * @param string $name Key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getGlobal($name)
    {
        if (!isset($this->defaults['global']) || !isset($this->defaults['global'][$name])) {
            throw new \InvalidArgumentException('View global value is not specified');
        }

        return $this->defaults['global'][$name];
    }

    /**
     * Call view helper using $this->{helperName}({args...}) in templates
     *
     * @param $name Helper name
     * @param $args Helper arguments
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);

        $method = mb_strtolower($name);
        $callback = array($helper, $method);

        if (!is_callable($callback)) {
            throw new \ErrorException(sprintf("View helper '%s' does not have accessible method: '%s", $name, $method));
        }

        return call_user_func_array($callback, $args);
    }

    /**
     * Set shared variable (accessible in base view and extended views)
     *
     * @param string $var  Variable name
     * @param mixed  $data Data
     */
    public function __set($var, $data)
    {
        $this->data[$var] = $data;
    }

    /**
     * Get shared variable (accessible in base view and extended views)
     *
     * @param string $var Variable name
     *
     * @return mixed
     */
    public function __get($var)
    {
        return $this->data[$var];
    }
}