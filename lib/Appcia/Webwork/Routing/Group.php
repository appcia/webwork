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
    private $routes;

    /**
     * Path prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Path suffix
     *
     * @var string
     */
    private $suffix;

    /**
     * Default module
     *
     * @var string
     */
    private $module;

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
     * @param string $module
     *
     * @return Group
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get default module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set path prefix
     *
     * @param string $prefix Prefix
     *
     * @return Group
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
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
     * @return Group
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
     * @return Group
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $key => $route) {
            if ($this->prefix !== null) {
                $route['path'] = $this->prefix . $route['path'];
            }
            if ($this->suffix !== null) {
                $route['path'] .= $this->suffix;
            }

            if ($this->module !== null && !isset($route['module'])) {
                $route['module'] = $this->module;
            }

            $routes[$key] = $route;
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
}