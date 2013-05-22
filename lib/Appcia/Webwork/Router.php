<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\Group;
use Appcia\Webwork\Router\Route;
use Appcia\Webwork\Data\TextCase;

class Router
{
    /**
     * All available routes
     *
     * @var array
     */
    private $routes;

    /**
     * Default values
     *
     * @var array
     */
    private $defaults;

    /**
     * Text case converter
     * Used for automatic route name generation
     *
     * @var TextCase
     */
    private $textCase;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = array();
        $this->defaults = array(
            'route' => array(
                'template' => '*.html.php'
            )
        );
        $this->textCase = new TextCase();
    }

    /**
     * Set default values
     *
     * @param array $data Data
     *
     * @return Router
     */
    public function setDefaults($data)
    {
        $this->defaults = $data;

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
     * Set text case converter
     * Used for automatic route name generation
     *
     * @param TextCase $textCase
     */
    public function setTextCase($textCase)
    {
        $this->textCase = $textCase;
    }

    /**
     * Get text case converter
     *
     * @return TextCase
     */
    public function getTextCase()
    {
        return $this->textCase;
    }

    /**
     * Set routes
     *
     * @param array $routes Routes
     *
     * @return Router
     * @throws Exception
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
     * @return Router
     * @throws Exception
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
     * Clear current routes
     *
     * @return Router
     */
    public function clearRoutes()
    {
        $this->routes = array();

        return $this;
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
     * Add route
     *
     * @param array $data Route data
     *
     * @return Router
     * @throws Exception
     */
    public function addRoute($data)
    {
        if (!is_array($data)) {
            throw new Exception('Route data should be an array');
        }

        if (!isset($data['path'])) {
            throw new Exception('Route path is not specified');
        }

        if (!isset($data['module'])) {
            throw new Exception('Route module is not specified');
        }

        if (!isset($data['controller'])) {
            throw new Exception('Route controller is not specified');
        }

        if (!isset($data['action'])) {
            throw new Exception('Route action is not specified');
        }

        if (!isset($data['name'])) {
            $data['name'] = $this->generateRouteName($data);
        }

        if (!empty($this->defaults['route'])) {
            $data = array_merge($this->defaults['route'], $data);
        }

        $route = new Route();

        $config = new Config($data);
        $config->inject($route);

        $name = $route->getName();
        $this->routes[$name] = $route;

        return $this;
    }

    /**
     * Generate route name basing on its specific data
     *
     * @param array $data Route data
     *
     * @return string
     */
    public function generateRouteName(array $data)
    {
        $parts = array_merge(
            explode('/', $data['module']),
            explode('/', $data['controller']),
            array($data['action'])
        );

        foreach ($parts as $key => $value) {
            $parts[$key] = $this->textCase->camelToDashed($value);
        }

        $name = implode('-', $parts);

        return $name;
    }


    /**
     * Get route by name
     *
     * @param string $name Name
     *
     * @return Route
     * @throws Exception
     */
    public function getRoute($name)
    {
        if (!isset($this->routes[$name])) {
            throw new Exception(sprintf("Route '%s' does not exist", $name));
        }

        $route = $this->routes[$name];

        return $route;
    }

    /**
     * Set routes using groups
     *
     * @param array $groups Data
     *
     * @return Router
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
     * @return Router
     * @throws Exception
     */
    public function addGroup(array $data)
    {
        if (!isset($data['routes'])) {
            throw new Exception('Route group has no routes specified');
        }

        if (!empty($this->defaults['group'])) {
            $data = array_merge($this->defaults['group'], $data);
        }

        $group = new Group();

        $config = new Config($data);
        $config->inject($group);

        $routes = $group->getRoutes();
        $this->setRoutes($routes);

        return $this;
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
        if ($request->getPath() == $route->getPath()) {
            return true;
        } else if ($route->hasParams()) {
            $match = array();
            if (preg_match($route->getPattern(), $request->getPath(), $match)) {
                unset($match[0]);

                $params = $this->retrieveParams($route, $match);
                $request->setParams($params);

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Retrieve route parameter names
     *
     * @param Route $route  Route
     * @param array $values Passed parameter values
     *
     * @return array
     * @throws Exception
     */
    private function retrieveParams(Route $route, array $values)
    {
        $config = $route->getParams();
        $names = array_keys($config);
        $params = array_combine($names, $values);

        foreach ($params as $name => $value) {
            if (!isset($config[$name])) {
                continue;
            }

            $data = $config[$name];

            // Apply default values
            if (isset($data['default'])) {
                $param = $data['default'];

                if (empty($value)) {
                    $params[$name] = $param;
                }
            }

            // Reverse map parameter names
            if (isset($data['map'])) {
                $map = $data['map'];

                if (!is_array($map)) {
                    throw new Exception('Route parameter map should be an array');
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

        return null;
    }

    /**
     * Assemble route by name with given parameters
     * If parameters are not in route path there are interpreted as GET params
     *
     * @param string $route Route name
     * @param array  $data  Path and GET parameters
     *
     * @return string
     * @throws Exception
     */
    public function assemble($route, array $data = array())
    {
        if (is_string($route)) {
            if (!isset($this->routes[$route])) {
                throw new Exception(sprintf("Route by name '%s' does not exist", $route));
            }

            $route = $this->routes[$route];
        } elseif (!$route instanceof Route) {
            throw new Exception('Route should be an existing route name or object');
        }

        // Prepare parameters, map and set defaults
        $params = array();
        foreach ($route->getParams() as $name => $config) {
            $value = null;

            if (isset($config['default'])) {
                $value = $config['default'];
            }

            if (isset($data[$name])) {
                $value = $data[$name];
                unset($data[$name]);
            }

            if ($value === null) {
                throw new Exception(sprintf("Route '%s' cannot be assembled when parameter '%s' is unmapped",
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

            $params['{' . $name . '}'] = $value;
        }

        $path = $route->getPath();
        $path = str_replace(array_keys($params), array_values($params), $path);

        if (!empty($data)) {
            $path .= '?' . http_build_query($data, null, '&amp;');
        }

        return $path;
    }
}
