<?

namespace Appcia\Webwork\Controller;

use Appcia\Webwork\Exception\Error;
use Appcia\Webwork\Exception\NotFound;
use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\View\View;
use Appcia\Webwork\Web\Context;
use Appcia\Webwork\Web\Request;
use Appcia\Webwork\Web\Response;

/**
 * Standard controller with wrappers for most commonly used staff
 *
 * @package Appcia\Webwork\Controller
 */
abstract class Fat extends Lite
{
    /**
     * Get a request
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->getApp()
            ->getRequest();
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->getApp()
            ->getConfig();
    }

    /**
     * @return Context
     */
    protected function getContext()
    {
        return $this->getApp()
            ->getContext();
    }

    /**
     * Get a view
     *
     * @return View
     */
    protected function getView()
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
    protected function goError($message = null)
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
    protected function goNotFound($message = null)
    {
        throw new NotFound($message);
    }

    /**
     * Refresh current action
     *
     * @return void
     */
    protected function goRefresh()
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
    protected function goRoute($route, array $params = array())
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
    protected function generateUrl($route, array $params = array())
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
    protected function goRedirect($url)
    {
        $this->getResponse()
            ->redirect($url);
    }

    /**
     * Get a response
     *
     * @return Response
     */
    protected function getResponse()
    {
        return $this->getApp()
            ->getDispatcher()
            ->getResponse();
    }
}