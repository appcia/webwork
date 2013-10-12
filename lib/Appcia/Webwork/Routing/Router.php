<?

namespace Appcia\Webwork\Routing;

use Appcia\Webwork\Routing\Group;
use Appcia\Webwork\Routing\Route;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Request;
use Appcia\Webwork\Model\Template;

/**
 * Processor which is matching request to route
 *
 * @package Appcia\Webwork\Routing
 */
class Router
{
    /**
     * All available routes
     *
     * @var array
     */
    protected $routes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array();
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
     * Set routes
     *
     * @param array $routes Routes
     *
     * @return $this
     */
    public function setRoutes(array $routes)
    {
        $this->clearRoutes()
            ->addRoutes($routes);

        return $this;
    }

    /**
     * Add routes
     *
     * @param array $routes Routes
     *
     * @return $this
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $name => $route) {
            if (!isset($route['name']) && is_string($name)) {
                $route['name'] = $name;
            }

            $this->addRoute($route);
        }

        return $this;
    }

    /**
     * Add route
     *
     * @param Route|array $route Route
     *
     * @return $this
     * @throws \LogicException
     */
    public function addRoute($route)
    {
        if (!$route instanceof Route) {
            $route = Route::create($route);
        }

        $name = $route->getName();
        if (isset($this->routes[$name])) {
            throw new \LogicException(sprintf("Route name '%s' is already used.", $name));
        }

        $this->routes[$name] = $route;

        $alias = $route->getAlias();
        if ($alias !== null) {
            if (isset($this->routes[$alias])) {
                throw new \LogicException(sprintf("Route alias '%s' is already used.", $alias));
            }

            $this->routes[$alias] = & $this->routes[$name];
        }

        return $this;
    }

    /**
     * Clear current routes
     *
     * @return $this
     */
    public function clearRoutes()
    {
        $this->routes = array();

        return $this;
    }

    /**
     * Set routes using groups
     *
     * @param array $groups Data
     *
     * @return $this
     */
    public function setGroups(array $groups)
    {
        foreach ($groups as $name => $group) {
            if (!isset($group['name']) && is_string($name)) {
                $group['name'] = $name;
            }

            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Add route group
     *
     * @param array $data Data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addGroup(array $data)
    {
        if (!isset($data['routes'])) {
            throw new \InvalidArgumentException('Route group has no routes specified.');
        }

        $group = new Group();

        $config = new Config($data);
        $config->inject($group);

        $routes = $group->getRoutes();
        $this->addRoutes($routes);

        return $this;
    }

    /**
     * Match requested URI to existing routes
     *
     * @param Request $request Source request
     *
     * @return Route|null
     */
    public function match(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($this->process($request, $route)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Process request path with route pattern, retrieve parameters
     *
     * @param Request $request Source request
     * @param Route   $route   Route to be processed
     *
     * @return boolean
     */
    protected function process($request, $route)
    {
        $path = $route->getPath();

        if (preg_match($path->getRegExp(), $request->getPath())) {
            foreach ($path->getSegments() as $segment) {
                $values = array();
                if (preg_match($segment->getRegExp(), $request->getPath(), $values)) {
                    unset($values[0]);

                    $values = array_values($values);
                    $names = array_keys($segment->getParams());

                    $params = array();
                    if (!empty($names) && !empty($values)) {
                        $params = $this->processParams($route, array_combine($names, $values));
                    }

                    $request->setParams($params);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process route parameters
     *
     * @param Route $route  Route
     * @param array $params Passed parameters
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function processParams(Route $route, array $params)
    {
        $config = $route->getParams();

        foreach ($params as $name => $value) {
            if (!isset($config[$name])) {
                continue;
            }

            $data = $config[$name];

            // Reverse map parameter names
            if (isset($data['map'])) {
                $map = $data['map'];

                if (!is_array($map)) {
                    throw new \InvalidArgumentException('Route parameter map should be an array.');
                }

                $param = array_search($value, $map);

                if ($param !== false) {
                    $params[$name] = $param;
                }
            }
        }

        return $params;
    }

    /**
     * Assemble route by name with given parameters
     * If parameters are not in route path there are interpreted as GET params
     *
     * @param string $route Route name
     * @param array  $data  Route path and GET parameters
     *
     * @return string
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function assemble($route, array $data = array())
    {
        $route = $this->getRoute($route);

        foreach ($route->getParams() as $name => $config) {
            if (!array_key_exists($name, $data) && isset($config['default'])) {
                $data[$name] = $config['default'];
            }

            if (array_key_exists($name, $data) && isset($config['map'])) {
                $map = $config['map'];

                if (isset($map[$data[$name]])) {
                    $data[$name] = $map[$data[$name]];
                }
            }
        }

        $segment = $this->getSegment($route, $data);

        $params = array_intersect_key($data, $segment->getParams());
        $get = array_diff($data, $params);

        $path = $segment->setParams($params)
            ->render();

        if (!empty($get)) {
            $path .= '?' . http_build_query($get, null, '&amp;');
        }

        return $path;
    }

    /**
     * Find route by name
     *
     * @param Route|string $route
     *
     * @return Route
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function getRoute($route)
    {
        if (is_string($route)) {
            if (!isset($this->routes[$route])) {
                throw new \OutOfBoundsException(sprintf("Route by name '%s' does not exist.", $route));
            }

            $route = $this->routes[$route];

            return $route;
        } elseif (!$route instanceof Route) {
            throw new \InvalidArgumentException('Route should be an existing route name or object.');
        }

        return $route;
    }

    /**
     * Find route segment that could have specified parameters
     *
     * @param Route $route  Route
     * @param array $params Route parameters
     *
     * @return Template
     * @throws \InvalidArgumentException
     */
    protected function getSegment(Route $route, array $params)
    {
        $segments = $route->getPath()
            ->getSegments();

        foreach ($segments as $segment) {
            $diff = array_diff_key($segment->getParams(), $params);

            if (empty($diff)) {
                return $segment;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            "Route '%s' does not have any segment that match specified parameters: '%s'.",
            $route->getName(),
            implode(', ', array_keys($params))
        ));
    }
}
