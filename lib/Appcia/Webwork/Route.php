<?

namespace Appcia\Webwork;

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
     */
    public function setName($name)
    {
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
     */
    public function setPath($path)
    {
        $this->path = (string) $path;

        // Retrieve param names from path
        $match = array();
        if (preg_match_all('/\{('. self::PARAM_CLASS .')\}/', $path, $match)) {
            $path = '/^' . preg_quote(preg_replace('/\{('. self::PARAM_CLASS .')\}/', self::PARAM_SUBSTITUTION, $path), '/') . '$/';
            $path = str_replace(self::PARAM_SUBSTITUTION, '('. self::PARAM_CLASS .')', $path);

            // Add params to map, null value means any possible
            foreach ($match[1] as $param) {
                if (!isset($this->params[$param])) {
                    $this->params[$param] = null;
                }
            }

            $this->pattern = $path;
        }

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
     * Set params map
     *
     * @param array $params Map
     *
     * @return Route
     */
    public function setParams(array $params)
    {
        // Merge, because cannot forget about params defined earlier in path
        $this->params = array_merge($this->params, $params);

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