<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Data\TextCase;
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
     * @param array    $data      Route data
     * @param TextCase $converter Text case converter
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create(array $data, TextCase $converter = null)
    {
        if ($converter === null) {
            $converter = new TextCase();
        }

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
            $parts = array_merge(
                explode('/', $data['module']),
                explode('/', $data['controller']),
                array($data['action'])
            );

            foreach ($parts as $key => $value) {
                $parts[$key] = $converter->camelToDashed($value);
            }

            $name = implode('-', $parts);

            $data['name'] = $name;
        }

        $route = new Route();

        $config = new Config($data);
        $config->inject($route);

        return $route;
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

        $data = Config::patternize($location, $params);

        if ($data !== false) {
            foreach ($data['params'] as $param) {
                if (!isset($params[$param])) {
                    $params[$param] = array();
                }
            }

            $this->pattern = $data['pattern'];
        }

        $this->path = $location;
        $this->params = $params;

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
     * Get action name
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
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
     * Get controller name
     *
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
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
     * Get template file to be rendered
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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

    /**
     * Get name alias
     *
     * @return null|string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}