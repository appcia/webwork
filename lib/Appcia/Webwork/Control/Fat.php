<?

namespace Appcia\Webwork\Control;

use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Web\Dispatcher;
use Appcia\Webwork\Web\Request;
use Appcia\Webwork\Web\Response;

/**
 * Standard control with wrappers for most commonly used staff
 */
abstract class Fat extends Lite
{
    /**
     * Get a request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->getApp()
            ->getRequest();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->getApp()
            ->getConfig();
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->getApp()
            ->getContext();
    }

    /**
     * Get a view
     *
     * @return View
     */
    public function getView()
    {
        return $this->getApp()
            ->getDispatcher()
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
        $route = $this->getApp()
            ->getDispatcher()
            ->getRoute()
            ->getName();

        $params = $this->getApp()
            ->getRequest()
            ->getUriParams();

        $this->goRoute($route, $params);
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
        return $this->getApp()
            ->getRouter()
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
        return $this->getApp()
            ->getDispatcher()
            ->getResponse();
    }

    /**
     * Set response content
     *
     * @param mixed $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->getDispatcher()
            ->setAutoRender(false);

        $this->getResponse()
            ->setContent($content);

        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->getApp()
            ->getDispatcher();
    }
}