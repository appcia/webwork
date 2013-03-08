<?

namespace Appcia\Webwork;

use Appcia\Webwork\Module;
use Appcia\Webwork\Router\Route;
use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Dispatcher\Listener;

class Dispatcher
{
    const PRE_DISPATCH = 'preDispatch';
    const ROUTING = 'routing';
    const ACTION_INVOKING = 'invoking';
    const RESPONSE_PROCESSING = 'processing';
    const EXCEPTION_HANDLING = 'exception';
    const POST_DISPATCH = 'postDispatch';

    /**
     * @var array
     */
    private $events = array(
        self::PRE_DISPATCH,
        self::ROUTING,
        self::ACTION_INVOKING,
        self::RESPONSE_PROCESSING,
        self::POST_DISPATCH
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
     * Dispatch by request
     *
     * @return Dispatcher
     * @throws Exception\NotFound
     * @throws \Exception
     */
    public function dispatch()
    {
        $this->container->set('exception', null);

        $this->notify(self::PRE_DISPATCH);

        try {
            $router = $this->container->get('router');
            $route = $router->match($this->request);

            if ($route === null) {
                throw new NotFound('Cannot find any route by request');
            }

            $this->setRoute($route);

            $this->notify(self::ROUTING)
                ->invokeAction()
                ->notify(self::ACTION_INVOKING)
                ->processResponse()
                ->notify(self::RESPONSE_PROCESSING);

            $this->invokeAction();
            $this->processResponse();
        } catch (\Exception $e) {
            $handled = false;

            foreach ($this->handlers as $handler) {
                $exception = $handler['exception'];
                $callback = $handler['callback'];

                if (get_class($e) === get_class($exception)) {
                    call_user_func_array($callback, array($this->container));
                    $handled = true;
                }
            }

            if (!$handled) {
                throw $e;
            }
        }

        $this->notify(self::POST_DISPATCH);

        return $this;
    }

    /**
     * Dispatch again by route (forced)
     *
     * @param string $routeName Route name
     *
     * @return Dispatcher
     * @throws Error
     */
    public function redispatch($routeName) {
        $router = $this->container->get('router');
        $route = $router->getRoute($routeName);

        if ($route === null) {
            throw new Error(sprintf("Cannot find route by name: '%s'", $routeName));
        }

        $this->setRoute($route);

        $this->invokeAction();
        $this->processResponse();

        return $this;
    }

    /**
     * Register exception handler
     *
     * @param \Exception $exception Exception object to handle
     * @param callable   $callback  Callback function
     * @throws Exception
     */
    public function handle($exception, \Closure $callback)
    {
        if (!is_object($exception)) {
            throw new Exception('Invalid exception to handle');
        }

        if (!is_callable($callback)) {
            throw new Exception('Listener callback is not callable');
        }

        $this->handlers[] = array(
            'exception' => $exception,
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
    public function listen($event, \Closure $callback)
    {
        if (!in_array($event, $this->events, true)) {
            throw new Exception(sprintf("Invalid event type: '%s'", $event));
        }

        if (!is_callable($callback)) {
            throw new Exception('Listener callback is not callable');
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