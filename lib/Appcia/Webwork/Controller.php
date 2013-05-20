<?

namespace Appcia\Webwork;

use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Exception\Error;

class Controller
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container Container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get DI container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get service or parameter from DI container
     *
     * @param string $key Service or parameter key
     *
     * @return mixed
     */
    protected function get($key)
    {
        return $this->container->get($key);
    }

    /**
     * Get a request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('dispatcher')
            ->getRequest();
    }

    /**
     * Get a response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('dispatcher')
            ->getResponse();
    }

    /**
     * Get a view
     *
     * @return View
     */
    public function getView()
    {
        return $this->get('dispatcher')
            ->getView();
    }

    /**
     * Trigger an error
     *
     * @param string $message Message
     *
     * @return void
     * @throws Error
     */
    public function goError($message = null)
    {
        throw new Error($message);
    }

    /**
     * Redirect to not found page
     *
     * @param string $message Message
     *
     * @return void
     * @throws NotFound
     */
    public function goNotFound($message = null)
    {
        throw new NotFound($message);
    }

    /**
     * Redirect to specified route (internally)
     *
     * @param string $route  Route name
     * @param array  $params Route params
     *
     * @return void
     */
    public function goRoute($route, array $params = array())
    {
        $url = $this->generateUrl($route, $params);
        $this->goRedirect($url);
    }

    /**
     * Redirect to absolute url (externally)
     *
     * @param string $url URL address
     *
     * @return void
     */
    public function goRedirect($url)
    {
        $this->getResponse()
            ->redirect($url);
    }

    /**
     * Refresh current action
     *
     * @return void
     */
    public function goRefresh()
    {
        $dispatcher = $this->get('dispatcher');
        $name = $dispatcher->getRoute()
            ->getName();

        $params = $dispatcher->getRequest()
                ->getUriParams();

        $this->goRoute($name, $params);
    }

    /**
     * Shortcut for generating url's
     *
     * @param string $route  Route name
     * @param array  $params Route params
     *
     * @return string
     */
    public function generateUrl($route, array $params = array())
    {
        return $this->get('router')
            ->assemble($route, $params);
    }
}