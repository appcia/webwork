<?

namespace Appcia\Webwork;

class Router
{
    /**
     * All available routes
     *
     * @var array
     */
    private $routes;

    /**
     * Default settings
     *
     * @var array
     */
    private $defaults;

    /**
     * Route name when none of routes match to request
     *
     * @var string
     */
    private $failRoute;

    /**
     * Route name when error occurred or exception thrown
     *
     * @var string
     */
    private $errorRoute;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->defaults = array();
        $this->routes = array();

        $this->failRoute = 'error_404';
        $this->errorRoute = 'error_500';
    }

    /**
     * Set default values
     *
     * @param array $defaults
     *
     * @return Router
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Get default values
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set route name for situation when some error occurred
     *
     * @param string $errorRoute
     *
     * @return Router
     */
    public function setErrorRoute($errorRoute)
    {
        $this->errorRoute = $errorRoute;

        return $this;
    }

    /**
     * Get route name for situation when some error occurred
     *
     * @return string
     */
    public function getErrorRoute()
    {
        return $this->errorRoute;
    }

    /**
     * Set route name for situation when none of routes match to request
     *
     * @param string $failRoute
     */
    public function setFailRoute($failRoute)
    {
        $this->failRoute = $failRoute;
    }

    /**
     * Get route name for situation when none of routes match to request
     *
     * @return string
     */
    public function getFailRoute()
    {
        return $this->failRoute;
    }

    /**
     * @param array $routes
     *
     * @return Router
     * @throws \InvalidArgumentException
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }

        return $this;
    }

    /**
     * Add route by an array
     * Could be overwritten
     *
     * @param array $route Route data
     *
     * @throws \InvalidArgumentException
     */
    public function addRoute(array $route)
    {
        $defaults = array();
        if (!empty($this->defaults['route'])) {
            $defaults = $this->defaults['route'];
        }

        if (!is_array($route)) {
            throw new \InvalidArgumentException('Route data is not an array');
        }

        if (!isset($route['name'])) {
            throw new \InvalidArgumentException('Route name is not specified');
        }

        $config = new Config($defaults);
        $config->extend(new Config($route));

        $route = new Route();
        $config->inject($route);

        $this->routes[$route->getName()] = $route;
    }

    /**
     * Get all routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Process request path with route pattern, retrieve parameters
     *
     * @param Request $request Source request
     * @param Route   $route   Route to be processed
     *
     * @return bool
     */
    private function process($request, $route)
    {
        if ($route->hasParams()) {
            $match = array();
            if (preg_match($route->getPattern(), $request->getPath(), $match)) {
                unset($match[0]);

                // Retrieve parameters
                $values = $match;
                $params = array_combine(array_keys($route->getParams()), $values);

                $request->setParams($params);

                return true;
            } else {
                // Invalid parameters / empty values
                return false;
            }
        } else if ($request->getPath() == $route->getPath()) {
            return true;
        }

        return false;
    }

    /**
     * Match requested URI to existing routes
     *
     * @param Request $request Source request
     *
     * @return array
     * @throws \LogicException
     */
    public function match(Request $request)
    {
        $failRoute = null;

        foreach ($this->routes as $name => $route) {

            // Look for fail route, remember it for later
            if ($name == $this->failRoute) {
                $failRoute = $route;
                continue;
            }

            // Process route and match, if valid return
            if ($this->process($request, $route)) {
                return $route;
            }
        }

        if (!$failRoute) {
            throw new \LogicException(sprintf("Fail route '%s' does not exist", $this->failRoute));
        }

        return $failRoute;
    }

    /**
     * Assemble route by name with given parameters
     * If parameters are not in route path there are interpreted as url GET params
     *
     * @param string  $route  Route name
     * @param array   $params Route or / and GET params
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function assemble($route, array $params = array())
    {
        if (!isset($this->routes[$route])) {
            throw new \InvalidArgumentException(sprintf("Route '%s' does not exist", $route));
        }

        $route = $this->routes[$route];

        // Share params to 2 types: path and GET
        $pathParams = array();
        $pathNames = array();
        $queryParams = array();
        $map = $route->getParams();

        foreach ($params as $name => $value) {
            if (array_key_exists($name, $map)) {
                // Use param map if exist (for translating param values)
                if (!empty($map[$name][$value])) {
                    $value = $map[$name][$value];
                }

                $pathParams[$name] = $value;
                $pathNames[] = '{' . $name . '}';

                unset($map[$name]);
            } else {
                $queryParams[$name] = $value;
            }
        }

        // Check that all params from map are used
        if (!empty($map)) {
            throw new \InvalidArgumentException(sprintf("Route parameter '%s' is not mapped (or it is redundant)", key($map)));
        }

        // Inject params to path
        $path = str_replace($pathNames, $pathParams, $route->getPath());

        // Append GET parameters
        if (!empty($queryParams)) {
            $path .= '?' . http_build_query($queryParams, null, '&amp;');
        }

        return $path;
    }
}
