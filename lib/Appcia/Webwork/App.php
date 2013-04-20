<?

namespace Appcia\Webwork;

use Appcia\Webwork\Bootstrap;
use Appcia\Webwork\Request;
use Appcia\Webwork\Response;

class App {

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
    public function __construct(Bootstrap $bootstrap) {
        $this->bootstrap = $bootstrap;
    }

    /**
     * Run in browser
     *
     * @return int
     */
    public function run()
    {
        $this->request = new Request();
        $this->request->loadGlobals();

        $this->response = new Response();

        $container = $this->bootstrap->getContainer();

        $router = $container->get('router');
        $route = $router->match($this->request);

        $dispatcher = $container->get('dispatcher')
            ->setRequest($this->request)
            ->setResponse($this->response)
            ->dispatch($route);

        $this->response->display();

        $status = $this->response->getStatus();

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