<?

namespace Appcia\Webwork;

class App
{
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * Constructor
     *
     * @param Bootstrap $bootstrap
     */
    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Run in browser
     *
     * @return int
     */
    public function run()
    {
        $request = new Request();
        $request->loadGlobals();

        $container = $this->bootstrap->getContainer();

        $router = $container->get('router');
        $route = $router->match($request);

        $response = $container->get('dispatcher')
            ->setRequest($request)
            ->dispatch($route)
            ->getResponse();

        $response->display();
        $status = $response->getStatus();

        $this->request = $request;
        $this->response = $response;

        return $status;
    }

    /**
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}