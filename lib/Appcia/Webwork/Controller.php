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
     * Get service from core container
     *
     * @param string $key Service key
     *
     * @return mixed
     */
    protected function get($key)
    {
        return $this->container->get($key);
    }

    /**
     * Shortcut to current request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get('dispatcher')
            ->getRequest();
    }

    /**
     * Shortcut to current response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('dispatcher')
            ->getResponse();
    }

    /**
     * Shortcut for triggering error
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
     * Shortcut for getting not found page
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
     * Shortcut for redirecting to specified route (internally)
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
     * Shortcut for redirecting to absolute url (externally)
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

        $params = array_merge(
            $dispatcher->getRequest()
                ->getGet(),
            $dispatcher->getRequest()
                ->getParams()
        );

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

    /**
     * Shortcut for changing template
     *
     * @param $file
     *
     * @return Controller
     */
    public function setTemplate($file)
    {
        $this->get('dispatcher')
            ->getRoute()
            ->setTemplate($file);

        return $this;
    }
}