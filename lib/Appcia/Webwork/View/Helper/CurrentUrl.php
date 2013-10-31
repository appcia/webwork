<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class CurrentUrl extends Helper
{
    /**
     * Caller
     *
     * @param array $params Params to be overridden
     *
     * @return string
     */
    public function currentUrl($params = array())
    {
        $app = $this->getView()
            ->getApp();

        $router = $app->getRouter();
        $dispatcher = $app->getDispatcher();

        $name = $dispatcher->getRoute()
            ->getName();

        $params = array_merge(
            $app->getRequest()->getUriParams(),
            $params
        );

        $url = $router->assemble($name, $params);

        return $url;
    }
}
