<?

namespace Appcia\Webwork;

class Controller
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get service or parameter from container
     *
     * @param int $id Service ID
     *
     * @return mixed
     */
    protected function get($id)
    {
        return $this->container[$id];
    }

    /**
     * Shortcut for current request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('dispatcher')->getRequest();
    }

    /**
     * Shortcut for current response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('dispatcher')->getResponse();
    }

    /**
     * Shortcut for redirecting
     *
     * @param string $route  Route name
     * @param array  $params Route params
     */
    public function go($route, array $params = array())
    {
        $url = $this->get('router')->assemble($route, $params);
        $this->getResponse()->redirect($url);
    }
}