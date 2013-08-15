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
        $this->routes[$name] = $route;

        $alias = $route->getAlias();

        if ($alias !== NULL) {
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
     * Get route by name
     *
     * @param string $name Name
     *
     * @return Route
     * @throws \OutOfBoundsException
     */
    public function getRoute($name)
    {
        if (!isset($this->routes[$name])) {
            throw new \OutOfBoundsException(sprintf("Route '%s' does not exist.", $name));
        }

        $route = $this->routes[$name];

        return $route;
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
     * @return array
     */
    public function match(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($this->process($request, $route)) {
                return $route;
            }
        }

        return NULL;
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
        if ($route->getPath()->getContent() == $request->getPath()) {
            return TRUE;
        } else if ($route->hasParams()) {
            $match = array();
            if (preg_match($route->getPattern(), $request->getPath(), $match)) {
                unset($match[0]);

                $params = $this->retrieveParams($route, $match);
                $request->setParams($params);

                return TRUE;
            } else {
                return FALSE;
            }
        }

        return FALSE;
    }

    /**
     * Retrieve route parameter names
     *
     * @param Route $route  Route
     * @param array $values Passed parameter values
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function retrieveParams(Route $route, array $values)
    {
        $config = $route->getParams();
        $names = array_keys($config);
        $missing = array_diff($names, $values);

        if (!empty($missing)) {
            throw new \InvalidArgumentException(sprintf(
                "Route '%s' has missing parameters '%s'.",
                $route->getName(),
                implode(', ', $missing)
            ));
        }

        $params = array_combine($names, $values);

        foreach ($params as $name => $value) {
            if (!isset($config[$name])) {
                continue;
            }

            $data = $config[$name];

            // Apply default values
            if (isset($data['default'])) {
                $param = $data['default'];

                if (!is_scalar($param) && $param !== NULL) {
                    throw new \InvalidArgumentException('Route parameter default value should be a scalar or null.');
                }

                if (empty($value)) {
                    $params[$name] = $param;
                }
            }

            // Reverse map parameter names
            if (isset($data['map'])) {
                $map = $data['map'];

                if (!is_array($map)) {
                    throw new \InvalidArgumentException('Route parameter map should be an array.');
                }

                $param = array_search($value, $map);

                if ($param !== FALSE) {
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
     * @param array  $data  Path and GET parameters
     *
     * @return string
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function assemble($route, array $data = array())
    {
        if (is_string($route)) {
            if (!isset($this->routes[$route])) {
                throw new \OutOfBoundsException(sprintf("Route by name '%s' does not exist.", $route));
            }

            $route = $this->routes[$route];
        } elseif (!$route instanceof Route) {
            throw new \InvalidArgumentException('Route should be an existing route name or object.');
        }

        // Prepare parameters, map and set defaults
        $template = new Template($route->getPath()->getContent());

        foreach ($route->getParams() as $name => $config) {
            $value = NULL;

            if (isset($config['default'])) {
                $value = $config['default'];
            }

            if (isset($data[$name])) {
                $value = $data[$name];
                unset($data[$name]);
            }

            if ($value === NULL) {
                throw new \InvalidArgumentException(sprintf(
                    "Route '%s' cannot be assembled. Parameter '%s' is not specified.",
                    $route->getName(),
                    $name
                ));
            }

            if (isset($config['map'])) {
                $map = $config['map'];

                if (isset($map[$value])) {
                    $value = $map[$value];
                }
            }

            $template->set($name, $value);
        }

        $path = $template->render();

        if (!empty($data)) {
            $path .= '?' . http_build_query($data, NULL, '&amp;');
        }

        return $path;
    }
}
