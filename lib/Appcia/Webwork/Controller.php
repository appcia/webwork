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
}