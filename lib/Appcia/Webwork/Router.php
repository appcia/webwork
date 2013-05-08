<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\Route;

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
     * @param array $routes
     *
     * @return Router
     * @throws Exception
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
     * @throws Exception
     */
    public function addRoute(array $route)
    {
        $defaults = array();
        if (!empty($this->defaults['route'])) {
            $defaults = $this->defaults['route'];
        }

        if (!is_array($route)) {
            throw new Exception('Route data is not an array');
        }

        if (!isset($route['name'])) {
            throw new Exception('Route name is not specified');
        }

        $config = new Config($defaults);
        $config->extend(new Config($route));

        $route = new Route();
        $config->inject($route);

        $name = $route->getName();
        $this->routes[$name] = $route;
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
     * Get route by name
     *
     * @param string $name Name
     *
     * @return Route
     * @throws Exception
     */
    public function getRoute($name) {
        if (!isset($this->routes[$name])) {
            throw new Exception(sprintf("Route '%s' does not exist", $name));
        }

        return $this->routes[$name];
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
