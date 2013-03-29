<?

namespace Appcia\Webwork;

use Appcia\Webwork\Module;
use Appcia\Webwork\Router\Route;
use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Dispatcher\Listener;

class Dispatcher
{
    const E_INIT = 'init';
    const E_FIND_ROUTE = 'route';
    const E_INVOKE_ACTION = 'invoke';
    const E_PROCESS_RESPONSE = 'process';
    const E_HANDLE_EXCEPTION = 'exception';
    const E_FINISH = 'finish';

    /**
     * @var array
     */
    private $events = array(
        self::E_INIT,
        self::E_FIND_ROUTE,
        self::E_INVOKE_ACTION,
        self::E_PROCESS_RESPONSE,
        self::E_FINISH
    );

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $listeners;

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

        $this->handlers = array();
        $this->listeners = array();
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
                "Could not dispatch '%s''. Check whether that controller method really exist",
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
     * @return string
     */
    private function getModuleDir()
    {
        return $this->container->get('bootstrap')
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

        $this->container->get('config')
            ->get('view')
            ->inject($view);

        // Response
        $this->response->setContent($view->render());

        $this->container->get('config')
            ->get('response')
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

        $this->container->set('exception', null);
        $this->notify(self::E_INIT);

        try {
            if ($route === null) {
                throw new NotFound('Route not found');
            }

            $this->setRoute($route)
                ->notify(self::E_FIND_ROUTE)
                ->invokeAction()
                ->notify(self::E_INVOKE_ACTION)
                ->processResponse()
                ->notify(self::E_PROCESS_RESPONSE);
        } catch (\Exception $e) {
            $this->container->set('exception', $e);

            if (!$this->handle($e)) {
                throw $e;
            }
        }

        $this->notify(self::E_FINISH);

        return $this;
    }

    /**
     * Handle exception catched by some dispatching
     * Returns true if handled by any handler
     *
     * @param \Exception $e Exception
     * @return bool
     */
    public function handle($e)
    {
        $handled = false;

        foreach ($this->handlers as $handler) {
            $exceptionClass = $handler['exceptionClass'];
            $callback = $handler['callback'];

            if (get_class($e) === $exceptionClass) {
                call_user_func_array($callback, array($this->container));
                $handled = true;
            }
        }

        return $handled;
    }

    /**
     * Register exception handler
     *
     * @param mixed $exception Exception object / or class name to handle
     * @param callable   $callback  Callback function
     * @throws Exception
     */
    public function addHandler($exception, \Closure $callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Handler callback is invalid');
        }

        $exceptionClass = null;
        if (is_string($exception)) {
            $exceptionClass = $exception;
        }
        else {
            if (!$exception instanceof \Exception) {
                throw new Exception('Invalid exception to be handled');
            }

            $exceptionClass = get_class($exception);
        }

        $this->handlers[] = array(
            'exceptionClass' => $exceptionClass,
            'callback' => $callback
        );
    }

    /**
     * Register event listener
     *
     * @param string   $event    Event type
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
     * Notify listeners about event
     *
     * @param $event
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

        $path = implode('/', $parts);

        return $path;
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