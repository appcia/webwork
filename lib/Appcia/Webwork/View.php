<?

namespace Appcia\Webwork;

use Appcia\Webwork\View\Helper;

/**
 * Views with shared variables, helper mechanism
 */
class View
{
    /**
     * DI container
     *
     * @var Container
     */
    private $container;

    /**
     * Data to be used in template file
     *
     * @var array
     */
    private $data;

    /**
     * Template file
     *
     * @var string
     */
    private $file;

    /**
     * Registered helpers
     *
     * @var array
     */
    private $helpers;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;

        $this->data = array();
        $this->helpers = array();
    }

    /**
     * Get a container
     * Can be used only in view created by dispatcher
     *
     * @return Container
     * @throws Exception
     */
    public function getContainer()
    {
        if ($this->container === null) {
            throw new Exception('Invalid use. There is no container associated with view');
        }

        return $this->container;
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
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set template file
     *
     * @param string $file Path
     *
     * @return View
     */
    public function setFile($file)
    {
        $this->file = (string) $file;

        return $this;
    }

    /**
     * Get template file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get path for views in current module
     *
     * @return string
     */
    public function getModulePath()
    {
        $dispatcher = $this->container->get('dispatcher');
        $path = $dispatcher->getModulePath() . '/view';

        return $path;
    }

    /**
     * Get content generated using data and template file
     *
     * @param string $file File path
     *
     * @return string
     * @throws Exception
     */
    public function render($file = null)
    {
        if ($file === null) {
            $file = $this->file;
        }

        if (!file_exists($file)) {
            $moduleFile = $this->getModulePath() . '/' . $file;

            if (!file_exists($moduleFile)) {
                throw new Exception(sprintf("View file not found: '%s'", $file));
            }

            $file = $moduleFile;
        }

        extract($this->data);

        ob_start();

        if ((@include $file) === false) {
            throw new Exception(sprintf("View file cannot be included properly: '%s'", $file));
        }

        $result = ob_get_clean();

        return $result;
    }

    /**
     * Create helper by name
     * Search for valid class name in all modules
     *
     * @param string $name Name
     *
     * @return mixed
     * @throws Exception
     */
    private function createHelper($name)
    {
        $class = 'Appcia\\Webwork\\View\\Helper\\' . ucfirst($name);
        if (class_exists($class)) {
            return new $class();
        }

        $modules = $this->getContainer()
            ->get('bootstrap')
            ->getModules();

        foreach ($modules as $module) {
            $class = $module->getNamespace() . '\\View\\Helper\\' . ucfirst($name);
            if (class_exists($class)) {
                return new $class();
            }
        }

        throw new Exception(sprintf("Helper '%s' cannot be created. There is no valid class in any module", $class));
    }

    /**
     * Get helper by name
     *
     * @param string $name Name
     *
     * @return Helper
     * @throws Exception
     */
    public function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            $context = $this->getContainer()
                ->get('context');

            $helper = $this->createHelper($name);
            $helper->setView($this)
                ->setContext($context);

            $this->helpers[$name] = $helper;
        }

        return $this->helpers[$name];
    }

    /**
     * Call view helper using $this->{helperName}({args...}) in templates
     *
     * @param $name Helper name
     * @param $args Helper arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);

        $method = mb_strtolower($name);
        $callback = array($helper, $method);

        if (!is_callable($callback)) {
            throw new Exception(sprintf("View helper '%s' does not have accessible method: '%s", $name, $method));
        }

        $result = call_user_func_array($callback, $args);

        return $result;
    }
}