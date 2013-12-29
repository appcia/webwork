<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Control\Lite;
use Appcia\Webwork\Core\Module;
use Appcia\Webwork\Core\Monitor;
use Appcia\Webwork\Data\Converter;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Routing\Route;
use Appcia\Webwork\View\View;

/**
 * Unit which is processing a route from request and producing a response
 *
 * @package Appcia\Webwork\Web
 */
class Dispatcher
{
    /**
     * Control callback names
     */
    const BEFORE = 'before';

    const AFTER = 'after';

    /**
     * Monitor events
     */
    const STARTED = 'dispatch started';

    const RESPONSE_CREATED = 'response created';

    const ROUTE_FOUND = 'route found';

    const VIEW_CREATED = 'view created';

    const ACTION_INVOKED = 'action invoked';

    const RESPONSE_PROCESSED = 'response processed';

    const EXCEPTION_CAUGHT = 'exception caught';

    const EXCEPTION_HANDLED = 'exception handled';

    const ENDED = 'dispatch ended';

    /**
     * Monitor events
     *
     * @var array
     */
    protected static $events = array(
        self::STARTED,
        self::RESPONSE_CREATED,
        self::ROUTE_FOUND,
        self::VIEW_CREATED,
        self::ACTION_INVOKED,
        self::RESPONSE_PROCESSED,
        self::EXCEPTION_CAUGHT,
        self::EXCEPTION_HANDLED,
        self::ENDED
    );

    /**
     * Application
     *
     * @var App
     */
    protected $app;

    /**
     * Event monitor
     *
     * @var Monitor
     */
    protected $monitor;

    /**
     * Current route
     *
     * @var Route
     */
    protected $route;

    /**
     * Current view
     *
     * @var View
     */
    protected $view;

    /**
     * Control unit
     *
     * @var Lite
     */
    protected $control;

    /**
     * View auto rendering
     *
     * @var boolean
     */
    protected $autoRender;

    /**
     * Current response
     *
     * @var Response
     */
    protected $response;

    /**
     * Caught exception
     *
     * @var array
     */
    protected $exception;

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->monitor = new Monitor($this, static::$events);
        $this->data = array();
        $this->autoRender = true;
    }

    /**
     * Get possible events
     *
     * @return array
     */
    public static function getEvents()
    {
        return static::$events;
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
     * Check whether view auto rendering state
     *
     * @return boolean
     */
    public function isAutoRender()
    {
        return $this->autoRender;
    }

    /**
     * Enable or disable view auto rendering
     *
     * @param boolean $flag Flag
     *
     * @return $this
     */
    public function setAutoRender($flag)
    {
        $this->autoRender = (bool) $flag;

        return $this;
    }

    /**
     * Dispatch specified route
     *
     * @param mixed $route Route object or name
     *
     * @return Response
     * @throws \Exception
     */
    public function dispatch($route)
    {
        $this->monitor->notify(self::STARTED);

        $response = null;
        $view = null;

        try {
            $this->forceRoute($route);
            $this->monitor->notify(self::ROUTE_FOUND);

            $response = new Response();
            $this->app->getConfig()
                ->grab('response')
                ->inject($response);

            $this->response = $response;
            $this->monitor->notify(self::RESPONSE_CREATED);

            $view = new View($this->app);
            $this->app->getConfig()
                ->grab('view')
                ->inject($view);

            $template = $this->getTemplatePath();
            $view->setTemplate($template);

            $this->view = $view;
            $this->monitor->notify(self::VIEW_CREATED);

            $this->runModule();
            $this->invokeControl();

            $view->addData($this->getData());
            $this->monitor->notify(self::ACTION_INVOKED);

            if ($this->autoRender && !$response->hasContent()) {
                $content = $view->render();
                $response->setContent($content);
            }
            $this->monitor->notify(self::RESPONSE_PROCESSED);

        } catch (\Exception $e) {
            // Clean buffers for sure
            if ($response !== null) {
                $response->clean();
            }

            // Prevent nested exceptions
            if ($this->exception !== null) {
                throw $e;
            } else {
                $this->exception = $e;
            }

            // Create proper response
            $this->monitor->notify(self::EXCEPTION_CAUGHT);
            $response = $this->app->getErrorHandler()
                ->react($e);
            $this->monitor->notify(self::EXCEPTION_HANDLED);
        }

        $this->monitor->notify(self::ENDED);

        return $response;
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
     * @param string|null $module  Module name
     * @param string|null $control Control name
     * @param string|null $action  Action name
     *
     * @return string
     */
    public function getTemplatePath($module = null, $control = null, $action = null)
    {
        $template = $this->getModulePath($module) . '/view/' . $this->getControlPath($control)
            . '/' . $this->getTemplateFilename($action);

        return $template;
    }

    /**
     * Get module path by name
     * If not specified, dispatched module name is used
     *
     * @param string|null $module Module name
     *
     * @return string
     */
    public function getModulePath($module = null)
    {
        if ($module === null) {
            $module = $this->route->getModule();
        }

        $path = $this->app->getModule($module)
            ->getPath();

        return $path;
    }

    /**
     * Get control path basing on current route
     * If not specified, dispatched control name is used
     *
     * @param null $control Control name
     *
     * @return string
     */
    public function getControlPath($control = null)
    {
        if ($control === null) {
            $control = $this->route->getControl();
        }

        $path = $this->getPath($control);

        return $path;
    }

    /**
     * Convert class with namespace to path
     *
     * @param string $class Class
     *
     * @return string
     */
    protected function getPath($class)
    {
        $converter = new Converter();
        $parts = explode('\\', rtrim($class, '\\'));

        foreach ($parts as $key => $part) {
            $parts[$key] = $converter->camelToDashed($part);
        }

        $path = implode('/', $parts);

        return $path;
    }

    /**
     * Get view template filename basing on current route
     * If template name contains '*' it will be replaced by route action
     * If action not specified, dispatched control name is used
     *
     * @param string|null $action Action name
     *
     * @return string
     */
    public function getTemplateFilename($action = null)
    {
        if ($action === null) {
            $action = $this->getPath($this->route->getAction());
        }

        $template = mb_strtolower(str_replace('*', $action, $this->route->getTemplate()));

        return $template;
    }

    /**
     * Invoke control action
     *
     * @return $this
     * @throws \ErrorException
     */
    protected function invokeControl()
    {
        if ($this->route === null) {
            throw new \ErrorException("Control cannot be created until route is not dispatched.");
        }

        $parts = explode('/', $this->route->getControl());
        foreach ($parts as $key => $part) {
            $parts[$key] = ucfirst($part);
        }
        $control = implode('\\', $parts);

        $class = ucfirst($this->route->getModule())
            . '\\Control\\' . $control . 'Control';

        if (!class_exists($class)) {
            throw new \ErrorException(sprintf(
                "Control '%s' could not be loaded. Check paths and autoloader configuration",
                $class
            ));
        }
        $control = new $class($this->app);

        $this->addData($this->invokeMethod($control, static::BEFORE, false));
        $this->addData($this->invokeMethod($control, $this->getControlMethod(), true));
        $this->addData($this->invokeMethod($control, static::AFTER, false));

        $this->control = $control;

        return $this;
    }

    /**
     * @return Lite
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Get control method name basing on current route
     *
     * @return string
     */
    protected function getControlMethod()
    {
        $method = lcfirst($this->route->getAction()) . 'Action';

        return $method;
    }

    /**
     * Run module based on current route
     *
     * @return Module
     */
    protected function runModule()
    {
        $moduleName = $this->getRoute()
            ->getModule();
        $module = $this->app->getModule($moduleName);
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
     * Add processed data
     *
     * @param array $data
     *
     * @return $this
     */
    protected function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Invoke control method
     *
     * @param Lite    $control Control
     * @param string  $method  Method name
     * @param boolean $verbose Error when method does not exist
     *
     * @return array
     * @throws \ErrorException
     */
    protected function invokeMethod(Lite $control, $method, $verbose)
    {
        $action = array($control, $method);
        $class = get_class($control);
        $name = $class . '::' . $method;
        $data = array();

        if (is_callable($action)) {
            $data = call_user_func($action);

            if ($data === null) {
                $data = array();
            } elseif (!is_array($data)) {
                throw new \ErrorException(sprintf(
                    "Control method '%s' should return values as array.",
                    $name
                ));
            }
        } elseif ($verbose) {
            throw new \ErrorException(sprintf(
                "Could not dispatch '%s''. Check whether control method '%s' really exist.",
                $name,
                $method
            ));
        }

        return $data;
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
     * Get current view
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
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
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get event monitor
     *
     * @return Monitor
     */
    public function getMonitor()
    {
        return $this->monitor;
    }
}