<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\NotFoundException;
use Appcia\Webwork\Router\ErrorException;

class Dispatcher
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Response
     */
    private $response;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->data = array();
    }

    /**
     * @param Request $request
     *
     * @return Dispatcher
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Route $route
     *
     * @return Dispatcher
     */
    private function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get dispatched route
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set processed data
     *
     * @param array $data
     *
     * @return Dispatcher
     */
    private function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add processed data
     *
     * @param array $data
     *
     * @return Dispatcher
     */
    private function addData(array $data)
    {
        $this->data = Config::merge($this->data, $data);

        return $this;
    }

    /**
     * Get processed data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Response $response
     *
     * @return Dispatcher
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get output response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set route for dispatching using router
     *
     * @return Dispatcher
     * @throws \ErrorException
     */
    private function findRoute()
    {
        $router = $this->container['router'];
        $route = $router->match($this->request);

        $this->setRoute($route);

        return $this;
    }

    /**
     * Set route for dispatching using router
     *
     * @param string $type Event route type
     *
     * @return Dispatcher
     * @throws \ErrorException
     */
    private function setEventRoute($type)
    {
        $router = $this->container['router'];
        $route = $router->getEventRoute($type);

        $this->setRoute($route);

        return $this;
    }

    /**
     * @return string
     */
    private function getControllerClass()
    {
        return ucfirst($this->route->getModule())
            . '\\Controller\\' . ucfirst($this->route->getController()) . 'Controller';
    }

    /**
     * @return string
     */
    private function getControllerMethod()
    {
        return lcfirst($this->route->getAction()) . 'Action';
    }

    /**
     * Invoke action
     *
     * @return Dispatcher
     * @throws \LogicException
     * @throws \ErrorException
     */
    private function invokeAction()
    {
        $className = $this->getControllerClass();
        $methodName = $this->getControllerMethod();

        $controller = new $className($this->container);

        $action = array($controller, $methodName);
        if (!is_callable($action)) {
            throw new \ErrorException(sprintf(
                "Could not dispatch '%s''. Check whether that controller method really exist",
                $className . '::' . $methodName
            ));
        }

        $before = array($controller, 'before');
        if (is_callable($before)) {
            $data = call_user_func($before);
            if ($data !== null) {
                if (!is_array($data)) {
                    throw new \LogicException("Controller 'before' method must return values as array");
                }
                $this->addData($data);
            }
        }

        $data = call_user_func($action);
        if ($data !== null) {
            if (!is_array($data)) {
                throw new \LogicException("Controller action must return values as array");
            }
            $this->addData($data);
        }

        $after = array($controller, 'after');
        if (is_callable($after)) {
            $data = call_user_func($after);
            if ($data !== null) {
                if (!is_array($data)) {
                    throw new \LogicException("Controller 'after' method must return values as array");
                }
                $this->addData($data);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getModuleDir()
    {
        return $this->container['bootstrap']
            ->getModule($this->route->getModule())
            ->getPath();
    }

    /**
     * @return string
     */
    private function getControllerDir()
    {
        return $this->getPath($this->route->getController());
    }

    /**
     * @return string
     */
    private function getTemplateFilename()
    {
        $action = $this->getPath($this->route->getAction());
        $template = mb_strtolower(str_replace('*', $action, $this->route->getTemplate()));

        return $template;
    }

    /**
     * Process data, make views, set response
     *
     * @return Dispatcher
     */
    private function processResponse()
    {
        // View
        $view = new View($this->container);

        $view->setFile($this->getModuleDir() . '/view/' . $this->getControllerDir() . '/' . $this->getTemplateFilename())
            ->setData($this->data);

        $this->container['config']
            ->get('view')
            ->inject($view);

        // Response
        $this->response->setContent($view->render());

        $this->container['config']
            ->get('response')
            ->inject($this->response);

        return $this;
    }

    /**
     * Dispatch request
     *
     * @return Dispatcher
     */
    public function dispatch()
    {
        try {
            $this->container['exception'] = null;

            $this->findRoute()
                ->invokeAction()
                ->processResponse();
        }
        catch (NotFoundException $e) {
            $this->setEventRoute('notFound')
                ->invokeAction()
                ->processResponse();
        }
        catch (\Exception $e) {
            $this->container['exception'] = $e;

            $this->setEventRoute('error')
                ->invokeAction()
                ->processResponse();
        }

        return $this;
    }

    /**
     * Convert class with namespace to path
     *
     * @param $class
     *
     * @return string
     */
    private function getPath($class)
    {
        $parts = explode('\\', rtrim($class, '\\'));

        foreach ($parts as $key => $part) {
            $parts[$key] = $this->camelCaseToDashed($part);
        }

        return implode('/', $parts);
    }

    /**
     * Convert camel cased text to dashed
     *
     * @param string $str        String to be parsed
     * @param bool   $firstUpper Uppercase first letter?
     *
     * @return string
     */
    private function camelCaseToDashed($str, $firstUpper = false)
    {
        $str = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $str));
        $str = $firstUpper ? ucfirst($str) : lcfirst($str);

        return $str;
    }
}