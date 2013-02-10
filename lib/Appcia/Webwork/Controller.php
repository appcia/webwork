<?

namespace Appcia\Webwork;

use Appcia\Webwork\Router\NotFoundException;
use Appcia\Webwork\Router\ErrorException;

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
     * Shortcut for getting not found page
     *
     * @param string $message Message
     *
     * @throws NotFoundException
     * @return void
     */
    public function goNotFound($message = null)
    {
        throw new NotFoundException($message);
    }

    /**
     * Shortcut for triggering error
     *
     * @param string $message Message
     *
     * @throws ErrorException
     * @return void
     */
    public function goError($message = null)
    {
        throw new ErrorException($message);
    }

    /**
     * Shortcut for redirecting to specified route (internally)
     *
     * @param string $route  Route name
     * @param array  $params Route params
     *
     * @return mixed
     */
    public function goRoute($route, array $params = array())
    {
        $url = $this->generateUrl($route, $params);
        $this->goRedirect($url);
    }

    /**
     * Shortcut for redirecting to absolute url (externally)
     *
     * @param $url
     */
    public function goRedirect($url)
    {
        $this->getResponse()
            ->redirect($url);
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
    public function setTemplate($file)
    {
        $this->get('dispatcher')
            ->getRoute()
            ->setTemplate($file);
    }
}