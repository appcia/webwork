<?

namespace Appcia\Webwork;

use Appcia\Webwork\Data\TextCase;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Module;
use Appcia\Webwork\Router\Route;

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
     * Catched exception
     *
     * @var array
     */
    private $exception;

    /**
     * Exceptions instead of PHP errors
     *
     * @var bool
     */
    private $exceptionOnError;

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
     * @var View
     */
    private $view;

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

    const START = 'dispatchStart';
    const CREATE_RESPONSE = 'createResponse';
    const FIND_ROUTE = 'findRoute';
    const CREATE_VIEW = 'createView';
    const INVOKE_ACTION = 'invokeAction';
    const PROCESS_RESPONSE = 'processResponse';
    const HANDLE_EXCEPTION = 'handleException';
    const END = 'dispatchEnd';

    /**
     * Event collection
     *
     * @var array
     */
    private $events = array(
        self::START,
        self::CREATE_RESPONSE,
        self::FIND_ROUTE,
        self::CREATE_VIEW,
        self::INVOKE_ACTION,
        self::PROCESS_RESPONSE,
        self::HANDLE_EXCEPTION,
        self::END
    );

    /**
     * Text case converter
     * Used for file names determining
     *
     * @var TextCase
     */
    private $textCase;

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
        $this->exception = null;
        $this->exceptionOnError = false;
        $this->setExceptionOnError(true);
        $this->textCase = new TextCase();
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
     * @param Route|string $route Route object or name
     *
     * @return Dispatcher
     * @throws NotFound
     */
    public function setRoute($route)
    {
        if (is_string($route)) {
            $router = $this->container->get('router');
            $route = $router->getRoute($route);
        }

        if ($route === null) {
            throw new NotFound('Cannot dispatch. Route not found');
        }

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
        $this->data = array_merge($this->data, $data);

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
     * Set text case converter
     * Used for file names determining
     *
     * @param TextCase $converter
     *
     * @return Dispatcher
     */
    public function setTextCase($converter)
    {
        $this->textCase = $converter;

        return $this;
    }

    /**
     * @return TextCase
     */
    public function getTextCase()
    {
        return $this->textCase;
    }

    /**
     * Get captured exception
     *
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
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
     * Create a response
     *
     * @return Dispatcher
     */
    private function createResponse()
    {
        if ($this->response !== null) {
            $this->response->clean();
        }

        $response = new Response();

        $this->container->get('config')
            ->grab('response')
            ->inject($response);

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
     * Set view
     *
     * @param View $view View
     *
     * @return Dispatcher
     */
    public function setView(View $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Create a view
     *
     * @return Dispatcher
     */
    private function createView()
    {
        // Create view
        $view = new View($this->container);

        $template = $this->getModulePath() . '/view/' . $this->getControllerPath() . '/' . $this->getTemplateFilename();
        $view->setTemplate($template);

        $this->container->get('config')
            ->grab('view')
            ->inject($view);

        $this->view = $view;

        return $this;
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
        $this->view->addData($this->data);

        if (!$this->response->hasContent()) {
            $content = $this->view->render();
            $this->response->setContent($content);
        }

        return $this;
    }

    /**
     * Dispatch specified route
     *
     * @param mixed $route Route object or name
     *
     * @return Dispatcher
     * @throws NotFound
     * @throws Exception
     */
    public function dispatch($route)
    {
        $this->response = null;
        $this->view = null;

        // Dispatch route
        $this->notify(self::START);

        try {
            $this->createResponse()
                ->notify(self::CREATE_RESPONSE)
                ->setRoute($route)
                ->notify(self::FIND_ROUTE)
                ->createView()
                ->notify(self::CREATE_VIEW)
                ->invokeAction()
                ->notify(self::INVOKE_ACTION)
                ->processResponse()
                ->notify(self::PROCESS_RESPONSE);
        } catch (\Exception $e) {
            $this->response->clean();

            $this->handle($e)
                ->notify(self::HANDLE_EXCEPTION);
        }

        $this->notify(self::END);

        return $this;
    }

    /**
     * Call handler on exception
     *
     * @param \Exception $e Exception
     *
     * @return Dispatcher
     * @throws \Exception
     */
    private function handle($e)
    {
        // Prevent nested exceptions
        if ($this->exception !== null) {
            throw $e;
        }

        // Find best
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

        if ($handler === null) {
            throw $e;
        }

        // Call handler
        $this->exception = $e;
        call_user_func_array($handler['callback'], array($this->container));

        return $this;
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
    private function notify($event)
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
            $parts[$key] = $this->textCase->camelToDashed($part);
        }

        $path = implode('/', $parts);

        return $path;
    }

    /**
     * Callback for throwing exception on error
     *
     * @param int    $no      Error number
     * @param string $message Error Message
     * @param string $path    File path
     * @param int    $line    Line number
     *
     * @throws \ErrorException
     */
    public function throwExceptionOnError($no, $message, $path, $line)
    {
        throw new \ErrorException($message, $no, 0, $path, $line);
    }

    /**
     * Turn on / off exceptions on error
     *
     * @param bool $flag Flag
     *
     * @return Dispatcher
     */
    public function setExceptionOnError($flag)
    {
        if ($this->exceptionOnError) {
            restore_error_handler();
        }

        if ($flag) {
            set_error_handler(array($this, 'throwExceptionOnError'));
        }

        $this->exceptionOnError = $flag;

        return $this;
    }

    /**
     * Check whether exception will be thrown on error
     *
     * @return bool
     */
    public function isExceptionOnError()
    {
        return $this->exceptionOnError;
    }
}