<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Data\Converter;
use Appcia\Webwork\Model\Template;
use Appcia\Webwork\Storage\Config;

/**
 * Associates URI address with action to be executed
 *
 * @package Appcia\Webwork\Routing
 */
class Route
{
    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Path representation
     *
     * @var Path
     */
    protected $path;

    /**
     * Module name
     *
     * @var string
     */
    protected $module;

    /**
     * Controller name
     *
     * @var string
     */
    protected $controller;

    /**
     * Action name
     *
     * @var string
     */
    protected $action;

    /**
     * Template file to be rendered
     *
     * @var string
     */
    protected $template;

    /**
     * Parameter names
     *
     * @var array
     */
    protected $params;

    /**
     * Alias for name
     *
     * @var string|null
     */
    protected $alias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->params = array();
        $this->template = '*.html.php';
    }

    /**
     * Creator
     *
     * @param mixed $data Route data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        if (!isset($data['name'])) {
            $data['name'] = self::generateName(
                $data['module'],
                $data['controller'],
                $data['action']
            );
        }

        return Config::create($data, get_called_class());
    }

    /**
     * Generate unique name
     *
     * @param string      $module     Module name
     * @param string      $controller Controller path (or null for only module name)
     * @param string|null $action     Action name (or null for only controller name)
     *
     * @return string
     */
    public static function generateName($module, $controller = null, $action = null)
    {
        $parts = explode('/', $module);

        if ($controller !== null) {
            $parts = array_merge($parts, explode('/', $controller));
        }

        if ($action !== null) {
            $parts = array_merge($parts, array($action));
        }

        $converter = new Converter();
        foreach ($parts as $key => $value) {
            $parts[$key] = $converter->camelToDashed($value);
        }

        $name = implode('-', $parts);

        return $name;
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
     * Set name
     *
     * @param string $name
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Route name cannot be empty.');
        }

        $this->name = (string) $name;

        return $this;
    }

    /**
     * Get path
     *
     * @return Path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param string $path Path
     *
     * @return $this
     */
    public function setPath($path)
    {
        if (!$path instanceof Path) {
            $path = new Path($this, $path);
        }

        $params = array_keys($path->getParams());
        foreach ($params as $param) {
            if (!isset($this->params[$param])) {
                $this->params[$param] = array();
            }
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Pattern for retrieving params from request
     * Used by router if route has any params
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->path->getRegExp();
    }

    /**
     * Set parameter config
     *
     * @param array $params Config
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get parameter names
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get action name
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action name
     *
     * @param $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = (string) $action;

        return $this;
    }

    /**
     * Get unique action name
     *
     * @return string
     */
    public function getActionName()
    {
        $name = $this->generateName($this->module, $this->controller, $this->action);

        return $name;
    }

    /**
     * Get controller name
     *
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set controller name
     *
     * @param $controller
     *
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = (string) $controller;

        return $this;
    }

    /**
     * Generate unique controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        $name = $this->generateName($this->module, $this->controller);

        return $name;
    }

    /**
     * Get module name
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set module name
     *
     * @param $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = (string) $module;

        return $this;
    }

    /***
     * @return string
     */
    public function getModuleName()
    {
        return $this->generateName($this->module);
    }

    /**
     * Get template file to be rendered
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template file to be rendered
     *
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * Get name alias
     *
     * @return null|string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set name alias
     *
     * @param null|string $alias Alias
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function setAlias($alias)
    {
        if (empty($alias)) {
            throw new \InvalidArgumentException('Route alias cannot be empty.');
        }

        $this->alias = $alias;

        return $this;
    }
}