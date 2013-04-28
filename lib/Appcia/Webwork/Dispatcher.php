<?

namespace Appcia\Webwork;

use Appcia\Webwork\Module;
use Appcia\Webwork\Router\Route;
use Appcia\Webwork\Exception\NotFound;

class Dispatcher
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Dispatching event listeners
     *
     * @var array
     */
    private $listeners;

    /**
     * Specific exception handlers
     *
     * @var array
     */
    private $handlers;

    /**
     * Handler for all exceptions
     *
     * @var \Closure
     */
    private $handler;

    /**
     * Current request
     *
     * @var Request
     */
    private $request;

    /**
     * Matched route
     *
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $data;

    /**
     * Current response
     *
     * @var Response
     */
    private $response;

    const INIT = 'init';
    const FIND_ROUTE = 'route';
    const INVOKE_ACTION = 'invoke';
    const PROCESS_RESPONSE = 'process';
    const HANDLE_EXCEPTION = 'exception';
    const FINISH = 'finish';

    /**
     * Event collection
     *
     * @var array
     */
    private $events = array(
        self::INIT,
        self::FIND_ROUTE,
        self::INVOKE_ACTION,
        self::PROCESS_RESPONSE,
        self::FINISH
    );

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->data = array();

        $this->handlers = array();
        $this->listeners = array();
        $this->exceptions = array();
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
     * Get current request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Force route
     *
     * @param Route $route Route
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
     * Get captured exceptions
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * Get possible events
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get controller class name basing on current route
     *
     * @return string
     */
    private function getControllerClass()
    {
        $parts = explode('/', $this->route->getController());
        foreach ($parts as $key => $part) {
            $parts[$key] = ucfirst($part);
        }
        $controller = implode('\\', $parts);

        $class = ucfirst($this->route->getModule())
            . '\\Controller\\' . $controller . 'Controller';

        return $class;
    }

    /**
     * Get controller method name basing on current route
     *
     * @return string
     */
    private function getControllerMethod()
    {
        $method = lcfirst($this->route->getAction()) . 'Action';

        return $method;
    }

    /**
     * Run module based on current route
     *
     * @return Module
     */
    private function runModule()
    {
        $bootstrap = $this->container->get('bootstrap');
        $moduleName = $this->getRoute()->getModule();

        $app = $bootstrap->getModule('app');
        $module = $bootstrap->getModule($moduleName);

        $app->run();
        $module->run();

        return $module;
    }

    /**
     * Invoke action
     *
     * @return Dispatcher
     * @throws Exception
     */
    private function invokeAction()
    {
        $className = $this->getControllerClass();
        $methodName = $this->getControllerMethod();

        if (!class_exists($className)) {
            throw new Exception(sprintf(
                "Controller '%s' could not be loaded. Check paths and autoloader configuration",
                $className
            ));
        }

        $module = $this->runModule();
        $controller = new $className($this->container, $module->getContainer());

        $action = array($controller, $methodName);
        if (!is_callable($action)) {
            throw new Exception(sprintf(
                "Could not dispatch '%s''. Check whether controller method really exist",
                $className . '::' . $methodName
            ));
        }

        $data = call_user_func($action);
        if ($data !== null) {
            if (!is_array($data)) {
                throw new Exception("Controller action must return values as array");
            }
            $this->addData($data);
        }

        return $this;
    }

    /**
     * Get module path basing on current route
     *
     * @return string
     */
    public function getModulePath()
    {
        return $this->container->get('bootstrap')
            ->getModule($this->route->getModule())
            ->getPath();
    }

    /**
     * Get controller path basing on current route
     *
     * @return string
     */
    public function getControllerPath()
    {
        return $this->getPath($this->route->getController());
    }

    /**
     * Get view template filename basing on current route
     * If template name contains '*' it will be replaced by route action
     *
     * @return string
     */
    public function getTemplateFilename()
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

        $file = $this->getModulePath() . '/view/' . $this->getControllerPath() . '/' . $this->getTemplateFilename();

        $view->setFile($file)
            ->setData($this->data);

        $this->container->get('config')
            ->grab('view')
            ->inject($view);

        // Response
        $this->response->setContent($view->render());

        $this->container->get('config')
            ->grab('response')
            ->inject($this->response);

        return $this;
    }

    /**
     * Dispatch specified route
     *
     * @param mixed $route Route object or name
     *
     * @return Dispatcher
     * @throws NotFound
     * @throws \Exception
     */
    public function dispatch($route)
    {
        if (is_string($route)) {
            $router = $this->container->get('router');
            $route = $router->getRoute($route);
        }

        $this->notify(self::INIT);

        try {
            if ($route === null) {
                throw new NotFound('Cannot dispatch. Route not found');
            }

            $this->setRoute($route)
                ->notify(self::FIND_ROUTE)
                ->invokeAction()
                ->notify(self::INVOKE_ACTION)
                ->processResponse()
                ->notify(self::PROCESS_RESPONSE);
        } catch (\Exception $e) {
            $this->exceptions[] = $e;

            if (!$this->handle($e)) {
                throw $e;
            }
        }

        $this->notify(self::FINISH);

        return $this;
    }

    /**
     * Do handler action if exception occurred
     *
     * @param \Exception $e Exception
     * @return bool
     */
    public function handle($e)
    {
        $exception = get_class($e);
        $specificHandler = null;
        $allHandler = null;

        foreach ($this->handlers as $handler) {
            if ($handler['exception'] === true) {
                $allHandler = $handler;
            }

            if ($handler['exception'] === $exception) {
                $specificHandler = $handler;
            }
        }

        $handler = $allHandler;
        if ($specificHandler !== null) {
            $handler = $specificHandler;
        }

        if ($handler !== null) {
            call_user_func_array($handler['callback'], array($this->container));
            return true;
        }

        return false;
    }

    /**
     * Register exception handler
     *
     * Exception could be:
     * - object     for example: new Appcia\Webwork\NotFound\NotFound()
     * - class name for example: Appcia\Webwork\NotFound
     * - bool       if should always / never handle any type of exception
     *
     * @param mixed    $exception Exception to be handled, see description!
     * @param callable $callback  Callback function
     *
     * @return Dispatcher
     * @throws Exception
     */
    public function addHandler($exception, \Closure $callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Handler callback is invalid');
        }

        if (is_object($exception)) {
            if (!$exception instanceof \Exception) {
                throw new Exception('Invalid exception to be handled');
            }

            $exception = get_class($exception);
        }

        $this->handlers[] = array(
            'exception' => $exception,
            'callback' => $callback
        );

        return $this;
    }

    /**
     * Set handler for all exceptions
     *
     * @param callable $callback
     */
    public function setHandler(\Closure $callback)
    {
        $this->handler = $callback;
    }

    /**
     * Register event listener
     *
     * @param string   $event    Event
     * @param \Closure $callback Callback
     *
     * @return Dispatcher
     * @throws Exception
     */
    public function addListener($event, \Closure $callback)
    {
        if (!in_array($event, $this->events, true)) {
            throw new Exception(sprintf("Invalid event to be listened: '%s'", $event));
        }

        if (!is_callable($callback)) {
            throw new Exception('Listener callback is invalid');
        }

        $this->listeners[] = array(
            'event' => $event,
            'callback' => $callback
        );

        return $this;
    }

    /**
     * Notify listeners about dispatching event
     *
     * @param string $event Event
     *
     * @return Dispatcher
     */
    public function notify($event)
    {
        foreach ($this->listeners as $listener) {
            if ($listener['event'] === $event) {
                call_user_func_array($listener['callback'], array($this->container));
            }
        }

        return $this;
    }

    /**
     * Convert class with namespace to path
     *
     * @param string $class Class
     *
     * @return string
     */
    private function getPath($class)
    {
        $parts = explode('\\', rtrim($class, '\\'));

        foreach ($parts as $key => $part) {
            $parts[$key] = $this->camelCaseToDashed($part);
        }

        $path = implode('/', $parts);

        return $path;
    }

    /**
     * Convert camel cased text to dashed
     *
     * @param string $str      String to be parsed
     * @param bool $firstUpper Uppercase first letter?
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