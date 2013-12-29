<?

namespace Appcia\Webwork\Routing;

/**
 * Common configuration for a lot of routes
 *
 * @package Appcia\Webwork\Routing
 */
class Group
{
    /**
     * Routes data
     *
     * @var array
     */
    protected $routes;

    /**
     * Path prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Path suffix
     *
     * @var string
     */
    protected $suffix;

    /**
     * Default control
     *
     * @var string
     */
    protected $control;

    /**
     * Default module
     *
     * @var string
     */
    protected $module;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array();
    }

    /**
     * Set default module
     *
     * @param string $module Name
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get default module name
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set default control name
     *
     * @param string $control Name
     *
     * @return $this
     */
    public function setControl($control)
    {
        $this->control = $control;

        return $this;
    }

    /**
     * Get default control name
     *
     * @return string
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Set path prefix
     *
     * @param string $prefix Prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get path prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set path suffix
     *
     * @param string $suffix Suffix
     *
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get path suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Set routes data
     *
     * @param array $routes Routes data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $key => $route) {
            $routes[$key] = $this->processRoute($route);
        }

        $this->routes = $routes;

        return $this;
    }

    /**
     * Get routes data
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Process route path
     *
     * @param string $path Path
     *
     * @return string
     */
    protected function processPath($path)
    {
        if ($this->prefix !== null) {
            $path = $this->prefix . $path;
        }

        if ($this->suffix !== null) {
            $path .= $this->suffix;
        }

        return $path;
    }

    /**
     * Process route data
     *
     * @param Route $route Route
     *
     * @return Route
     * @throws \InvalidArgumentException
     */
    protected function processRoute($route)
    {
        // Path prefix, suffix
        if (!isset($route['path'])) {
            throw new \InvalidArgumentException("Route path is not specified.");
        }

        $route['path'] = $this->processPath($route['path']);

        // Module name completion
        if ($this->module !== null && !isset($route['module'])) {
            $route['module'] = $this->module;
        }

        // Control name completion
        if ($this->control !== null && !isset($route['control'])) {
            $route['control'] = $this->control;
            return $route;
        }

        return $route;
    }
}