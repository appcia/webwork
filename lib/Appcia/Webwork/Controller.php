<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\NotFoundException;

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
     * Shortcut to current request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('dispatcher')->getRequest();
    }

    /**
     * Shortcut to current response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('dispatcher')->getResponse();
    }

    /**
     * Shortcut for getting not found page
     *
     * @throws NotFoundException
     * @return void
     */
    public function goNotFound() {
        throw new NotFoundException();
    }

    /**
     * Shortcut for redirecting to specified route (internally)
     *
     * @param string $route  Route name
     * @param array  $params Route params
     *
     * @return mixed
     */
    public function goRoute($route, array $params = array()) {
        $this->goRedirect($this->generateUrl($route, $params));
    }

    /**
     * Shortcut for redirecting to absolute url (externally)
     *
     * @param $url
     */
    public function goRedirect($url) {
        $this->getResponse()->redirect($url);
    }

    /**
     * Shortcut for generating url's
     *
     * @param string $route  Route name
     * @param array  $params Route params
     */
    public function generateUrl($route, array $params = array())
    {
        return $this->get('router')
            ->assemble($route, $params);
    }

    /**
     * Shortcut for changing template
     *
     * @param $file
     */
    public function changeTemplate($file) {
        $this->get('dispatcher')
            ->getRoute()
            ->setTemplate($file);
    }
}