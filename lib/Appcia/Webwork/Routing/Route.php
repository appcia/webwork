<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Data\TextCase;
use Appcia\Webwork\Model\Pattern;
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
    private $name;

    /**
     * Path for router
     *
     * @var string
     */
    private $path;

    /**
     * Module name
     *
     * @var string
     */
    private $module;

    /**
     * Controller name
     *
     * @var string
     */
    private $controller;

    /**
     * Action name
     *
     * @var string
     */
    private $action;

    /**
     * Template file to be rendered
     *
     * @var string
     */
    private $template;

    /**
     * Parameter names
     *
     * @var array
     */
    private $params;

    /**
     * Pattern for retrieving parameters
     *
     * @var string
     */
    private $pattern;

    /**
     * Alias for name
     *
     * @var string|null
     */
    private $alias;

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
     * @param array $data Route data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create(array $data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Route data should be an array');
        }

        if (!isset($data['path'])) {
            throw new \InvalidArgumentException('Route path is not specified');
        }

        if (!isset($data['module'])) {
            throw new \InvalidArgumentException('Route module is not specified');
        }

        if (!isset($data['controller'])) {
            throw new \InvalidArgumentException('Route controller is not specified');
        }

        if (!isset($data['action'])) {
            throw new \InvalidArgumentException('Route action is not specified');
        }

        if (!isset($data['name'])) {
            $data['name'] = self::generateName(
                $data['module'],
                $data['controller'],
                $data['action']
            );
        }

        $route = new Route();

        $config = new Config($data);
        $config->inject($route);

        return $route;
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
        $parts = array_merge(
            explode('/', $module)
        );

        if ($controller !== null) {
            $parts = array_merge($parts, explode('/', $controller));
        }

        if ($action !== null) {
            $parts = array_merge($parts, array($action));
        }

        $converter = new TextCase();
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
            throw new \InvalidArgumentException('Route name cannot be empty');
        }

        $this->name = (string) $name;

        return $this;
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
     * Set path
     *
     * @param string $path
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setPath($path)
    {
        $location = null;
        $params = array();

        if (is_string($path)) {
            $location = $path;
        } else if (is_array($path)) {
            if (!isset($path['location'])) {
                throw new \InvalidArgumentException('Route location is not specified');
            }

            $location = $path['location'];

            if (isset($path['params'])) {
                if (!is_array($params)) {
                    throw new \InvalidArgumentException('Route parameters should be an array');
                }

                $params = $path['params'];
            }
        }

        if ($location !== '/') {
            $location = rtrim($location, '/');
        }

        $pattern = new Pattern($location);
        foreach ($pattern->getParams() as $param) {
            if (!isset($params[$param])) {
                $params[$param] = array();
            }
        }

        $this->pattern = $pattern->getRegExp();
        $this->path = $location;
        $this->params = $params;

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
        return $this->pattern;
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
     * Check whether route has any parameters in path
     *
     * @return boolean
     */
    public function hasParams()
    {
        return !empty($this->params);
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
            throw new \InvalidArgumentException('Route alias cannot be empty');
        }

        $this->alias = $alias;

        return $this;
    }
}