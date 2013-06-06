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
     * @return $this
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
     * @return $this
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

            // Path generation
            if (is_string($route['path'])) {
                $route['path'] = $this->processPath($route['path']);
            } elseif (is_array($route['path'])) {
                if (!isset($route['path']['location'])) {
                    throw new \InvalidArgumentException("Route path location is not specified");
                }

                $route['path']['location'] = $this->processPath($route['path']['location']);
            } else {
                throw new \InvalidArgumentException("Route path has invalid format");
            }

            // Module completion
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

    /**
     * Process route path
     *
     * @param string $path Path
     *
     * @return string
     */
    private function processPath($path)
    {
        if ($this->prefix !== null) {
            $path = $this->prefix . $path;
        }

        if ($this->suffix !== null) {
            $path .= $this->suffix;
        }

        return $path;
    }
}