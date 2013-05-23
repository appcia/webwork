<?

namespace Appcia\Webwork\Controller;

use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Web\Request;
use Appcia\Webwork\Web\Response;
use Appcia\Webwork\View\View;

abstract class Fat extends Lite
{
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
     * Get a response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get('dispatcher')
            ->getResponse();
    }
}