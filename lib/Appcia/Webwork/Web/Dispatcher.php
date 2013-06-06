<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Data\TextCase;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Routing\Route;
use Appcia\Webwork\View\View;
use Appcia\Webwork\Web\Response;

/**
 * Unit which is processing a route and producing a response
 *
 * @package Appcia\Webwork\Web
 */
class Dispatcher
{
    const START = 'dispatchStart';
    const CREATE_RESPONSE = 'createResponse';
    const FIND_ROUTE = 'findRoute';
    const CREATE_VIEW = 'createView';
    const INVOKE_ACTION = 'invokeAction';
    const PROCESS_RESPONSE = 'processResponse';
    const HANDLE_EXCEPTION = 'handleException';
    const END = 'dispatchEnd';

    /**
     * Application
     *
     * @var App
     */
    private $app;

    /**
     * Current route
     *
     * @var Route
     */
    private $route;

    /**
     * Current View
     *
     * @var View
     */
    private $view;

    /**
     * Current response
     *
     * @var Response
     */
    private $response;

    /**
     * Event listeners
     *
     * @var array
     */
    private $listeners;

    /**
     * Exception handlers
     *
     * @var array
     */
    private $handlers;

    /**
     * Caught exception
     *
     * @var array
     */
    private $exception;

    /**
     * Exceptions instead of PHP errors
     *
     * @var boolean
     */
    private $exceptionOnError;

    /**
     * Handler for all exceptions
     *
     * @var \Closure
     */
    private $handler;

    /**
     * @var array
     */
    private $data;

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
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->data = array();

        $this->handlers = array();
        $this->listeners = array();
        $this->exceptionOnError = false;
        $this->setExceptionOnError(true);
        $this->textCase = new TextCase();
    }

    /**
     * Turn on / off exceptions on error
     *
     * @param boolean $flag Flag
     *
     * @return $this
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
     * Get text case converter
     *
     * @return TextCase
     */
    public function getTextCase()
    {
        return $this->textCase;
    }

    /**
     * Set text case converter
     * Used for file names determining
     *
     * @param TextCase $converter
     *
     * @return $this
     */
    public function setTextCase($converter)
    {
        $this->textCase = $converter;

        return $this;
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
     * Dispatch specified route
     *
     * @param mixed $route Route object or name
     *
     * @return Response
     */
    public function dispatch($route)
    {
        $this->notify(self::START);

        $response = null;
        $view = null;

        try {
            $this->forceRoute($route);
            $this->notify(self::FIND_ROUTE);

            $response = new Response();
            $this->app->getConfig()
                ->grab('response')
                ->inject($response);

            $this->response = $response;
            $this->notify(self::CREATE_RESPONSE);

            $template = $this->getTemplatePath();
            $data = $this->invokeAction();
            $this->notify(self::INVOKE_ACTION);

            $view = new View($this->app);
            $this->app->getConfig()
                ->grab('view')
                ->inject($view);

            $view->setTemplate($template)
                ->addData($data);

            $this->view = $view;
            $this->notify(self::CREATE_VIEW);

            if (!$response->hasContent()) {
                $content = $view->render();
                $response->setContent($content);
            }
            $this->notify(self::PROCESS_RESPONSE);
        } catch (\Exception $e) {
            $this->notify(self::HANDLE_EXCEPTION);

            if ($response !== null) {
                $response->clean();
            }

            $response = $this->handle($e);
        }

        $this->notify(self::END);

        return $response;
    }

    /**
     * Notify listeners about dispatching event
     *
     * @param string $event Event
     *
     * @return $this
     */
    private function notify($event)
    {
        foreach ($this->listeners as $listener) {
            if ($listener['event'] === $event) {
                call_user_func_array($listener['callback'], array($this));
            }
        }

        return $this;
    }

    /**
     * Force route
     *
     * @param Route|string $route Route object or name
     *
     * @return $this
     * @throws NotFound
     */
    public function forceRoute($route)
    {
        if (is_string($route)) {
            $router = $this->app->getRouter();
            $route = $router->getRoute($route);
        }

        if ($route === null) {
            throw new NotFound('Dispatch error. Route not found');
        }

        $this->route = $route;

        return $this;
    }

    /**
     * Get template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $template = $this->getModulePath() . '/view/' . $this->getControllerPath() . '/' . $this->getTemplateFilename();

        return $template;
    }

    /**
     * Get module path basing on current route
     *
     * @return string
     */
    public function getModulePath()
    {
        $moduleName = $this->route->getModule();
        $path = $this->app->getModule($moduleName)
            ->getPath();

        return $path;
    }

    /**
     * Get controller path basing on current route
     *
     * @return string
     */
    public function getControllerPath()
    {
        $controllerName = $this->route->getController();
        $path = $this->getPath($controllerName);

        return $path;
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
     * Invoke controller action
     *
     * @return array
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

        $module = $this->runModule();
        $controller = new $className($this->app, $module->getApp());
        $action = array($controller, $methodName);

        if (!is_callable($action)) {
            throw new \ErrorException(sprintf(
                "Could not dispatch '%s''. Check whether controller method really exist",
                $className . '::' . $methodName
            ));
        }

        $data = call_user_func($action);

        if (!is_array($data)) {
            throw new \ErrorException("Controller action must return values as array");
        }

        return $data;
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
        $moduleName = $this->getRoute()
            ->getModule();

        $app = $this->app->getModule('app');
        $module = $this->app->getModule($moduleName);

        $app->run();
        $module->run();

        return $module;
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
     * Call handler on exception
     *
     * @param \Exception $e Exception
     *
     * @return Response
     * @throws \Exception
     */
    private function handle($e)
    {
        // Prevent nested exceptions
        if ($this->exception !== null) {
            throw $e;
        } else {
            $this->exception = $e;
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

        $response = call_user_func_array($handler['callback'], array($this));

        if (!$response instanceof Response) {
            throw new \ErrorException('Dispatch error handler should return response object');
        }

        return $response;
    }

    /**
     * Register exception handler
     *
     * Exception could be:
     * - class name for example: Appcia\Webwork\NotFound
     * - object     for example: new Appcia\Webwork\Exception\NotFound()
     * - boolean    if should always / never handle any type of exception
     *
     * @param mixed    $exception Exception to be handled, see description!
     * @param callable $callback  Callback function
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addHandler($exception, \Closure $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Handler callback is invalid');
        }

        if (is_object($exception)) {
            if (!$exception instanceof \Exception) {
                throw new \InvalidArgumentException('Invalid exception to be handled');
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
     * Get current view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set handler for all exceptions
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setHandler(\Closure $callback)
    {
        $this->handler = $callback;

        return $this;
    }

    /**
     * Register event listener
     *
     * @param string   $event    Event
     * @param \Closure $callback Callback
     *
     * @return $this
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function addListener($event, \Closure $callback)
    {
        if (!in_array($event, $this->events, true)) {
            throw new \OutOfBoundsException(sprintf("Invalid event to be listened: '%s'", $event));
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Listener callback is invalid');
        }

        $this->listeners[] = array(
            'event' => $event,
            'callback' => $callback
        );

        return $this;
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
     * Check whether exception will be thrown on error
     *
     * @return boolean
     */
    public function isExceptionOnError()
    {
        return $this->exceptionOnError;
    }

    /**
     * Get application
     *
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get response data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Add processed data
     *
     * @param array $data
     *
     * @return $this
     */
    private function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }
}