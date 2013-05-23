<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Exception\Exception;

class Route
{
    const PARAM_CLASS = '[A-Za-z0-9-]+';
    const PARAM_SUBSTITUTION = '___param___';

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
     * Constructor
     */
    public function __construct()
    {
        $this->params = array();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Route
     * @throws Exception
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new Exception('Route name cannot be empty');
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
     * @return Route
     * @throws Exception
     */
    public function setPath($path)
    {
        $location = null;
        $params = array();

        if (is_string($path)) {
            $location = $path;
        } else if (is_array($path)) {
            if (!isset($path['location'])) {
                throw new Exception('Route location is not specified');
            }
            
            $location = $path['location'];
            
            if (isset($path['params'])) {
                if (!is_array($params)) {
                    throw new Exception('Route parameters should be an array');
                }
                
                $params = $path['params'];
            }
        }

        if ($location !== '/') {
            $location = rtrim($location, '/');
        }

        $match = array();
        if (preg_match_all('/\{(' . self::PARAM_CLASS . ')\}/', $location, $match)) {
            $pattern = '/^' . preg_quote(preg_replace('/\{(' . self::PARAM_CLASS . ')\}/', self::PARAM_SUBSTITUTION, $location), '/') . '\/?$/';
            $pattern = str_replace(self::PARAM_SUBSTITUTION, '(' . self::PARAM_CLASS . ')', $pattern);

            foreach ($match[1] as $param) {
                if (!isset($params[$param])) {
                    $params[$param] = array();
                }
            }

            $this->pattern = $pattern;
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
     * @return bool
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
     * @return Route
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
     * @return Route
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
     * @return Route
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
     * @return Route
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
}