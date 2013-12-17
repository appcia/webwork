<?

namespace Appcia\Webwork\Web;

use Appcia\Webwork\Core\App as Base;
use Appcia\Webwork\Core\Bootstrap;
use Appcia\Webwork\Routing\Router;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Web\Dispatcher;

/**
 * Web application
 * To get a response: bootstrap, set a request and run...
 */
class App extends Base
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Config $config Configuration
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $this->dispatcher = new Dispatcher($this);
        $this->context = new Context();
        $this->router = new Router();
    }

    /**
     * Initialize whole application
     *
     * @return $this
     */
    public function bootstrap()
    {
        $this->loadModules();
        $this->applyConfig();

        return $this;
    }

    /**
     * Apply configuration
     *
     * @return $this
     */
    protected function applyConfig()
    {
        $this->config->grab('request')
            ->inject($this->request);

        $this->config->grab('router')
            ->inject($this->router);

        $this->config->grab('dispatcher')
            ->inject($this->dispatcher);

        $this->config->grab('context')
            ->inject($this->context);

        return $this;
    }

    /**
     * Run application
     *
     * @return Response
     * @throws \LogicException
     */
    public function run()
    {
        if ($this->request === null) {
            throw new \LogicException('Application run error. Request is not specified.');
        }

        $route = $this->router->match($this->request);
        $response = $this->dispatcher->dispatch($route);

        return $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Context $context
     *
     * @return $this
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Router $router
     *
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }
}