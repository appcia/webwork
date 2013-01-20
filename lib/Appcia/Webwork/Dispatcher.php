<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\NotFoundException;
use Appcia\Webwork\Router\ErrorException;

class Dispatcher
{
    const EVENT_START = 'start';
    const EVENT_ROUTED = 'routed';
    const EVENT_INVOKED = 'invoked';
    const EVENT_STOP = 'stop';
    const EVENT_ERROR = 'error';
    const EVENT_NOT_FOUND = 'notFound';

    /**
     * @var Container
     */
    private $container;

    private $eventListeners = array();

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
        $this->eventListeners = array();
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
     * Force set route
     *
     * Useful for event listeners
     *
     * @param Route $route
     *
     * @return Dispatcher
     */
    public function setRoute(Route $route)
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
        $parts = explode('/', $this->route->getController());
        foreach ($parts as $key => $part) {
            $parts[$key] = ucfirst($part);
        }
        $controller = implode('\\', $parts);

        return ucfirst($this->route->getModule())
            . '\\Controller\\' . $controller . 'Controller';
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

        if (!class_exists($className)) {
            throw new \ErrorException(sprintf(
                "Controller '%s' could not be loaded. Check paths and autoloader configuration",
                $className
            ));
        }

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

        $file = $this->getModuleDir() . '/view/' . $this->getControllerDir() . '/' . $this->getTemplateFilename();

        $view->setFile($file)
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

            $this->notifyEvent(self::EVENT_START)
                ->findRoute()
                ->notifyEvent(self::EVENT_ROUTED)
                ->invokeAction()
                ->notifyEvent(self::EVENT_INVOKED)
                ->processResponse();
        }
        catch (NotFoundException $e) {
            $this->notifyEvent(self::EVENT_NOT_FOUND)
                ->setEventRoute(Router::ROUTE_NOT_FOUND)
                ->invokeAction()
                ->processResponse();
        }
        catch (\Exception $e) {
            $this->container['exception'] = $e;

            $this->notifyEvent(self::EVENT_ERROR)
                ->setEventRoute(Router::ROUTE_ERROR)
                ->invokeAction()
                ->processResponse();
        }

        $this->notifyEvent(self::EVENT_STOP);

        return $this;
    }

    /**
     * Notify listeners about event
     *
     * @param $event
     *
     * @return Dispatcher
     */
    public function notifyEvent($event) {
        foreach ($this->eventListeners as $listener) {
            $listener->notify($event);
        }

        return $this;
    }

    /**
     * Register event listener
     *
     * @param Listener $listener
     *
     * @return Dispatcher
     * @throws \InvalidArgumentException
     */
    public function addEventListener(Listener $listener) {
        if (in_array($listener, $this->eventListeners)) {
            throw new \InvalidArgumentException("Specified listener is already registered");
        }

        $this->eventListeners[] = $listener;

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