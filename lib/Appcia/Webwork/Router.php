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
     * Add routes
     *
     * @param array $routes Routes
     *
     * @return Router
     * @throws Exception
     */
    public function setRoutes(array $routes)
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
     * Generate route name basing on its other data
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

        return $this->routes[$name];
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

                // Retrieve parameters
                $values = $match;
                $params = array_combine(array_keys($route->getParams()), $values);

                // Reverse map translated params
                foreach ($params as $key => $value) {
                    $map = $route->getParams();
                    if (array_key_exists($key, $map) && is_array($map[$key])) {
                        $param = array_search($value, $map[$key]);

                        if ($param !== false) {
                            $params[$key] = $param;
                        }
                    }
                }

                $request->setParams($params);

                return true;
            } else {
                // Invalid parameters / empty values
                return false;
            }
        }

        return false;
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
     * If parameters are not in route path there are interpreted as url GET params
     *
     * @param string  $route  Route name
     * @param array   $params Route or / and GET params
     *
     * @return string
     * @throws Exception
     */
    public function assemble($route, array $params = array())
    {
        if (!isset($this->routes[$route])) {
            throw new Exception(sprintf("Route '%s' does not exist", $route));
        }

        $route = $this->routes[$route];

        // Share params to 2 types: path and GET
        $pathParams = array();
        $pathNames = array();
        $queryParams = array();
        $map = $route->getParams();

        foreach ($params as $name => $value) {
            if (!is_scalar($value)) {
                throw new Exception(sprintf("Cannot use non-scalar value as route parameter '%s'", $name));
            }

            if (array_key_exists($name, $map)) {
                // Use param map if exist (for translating param values)
                if (is_array($map[$name]) && !empty($map[$name][$value])) {
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
            throw new Exception(sprintf("Route parameter '%s' is not mapped (or it is redundant)", key($map)));
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
