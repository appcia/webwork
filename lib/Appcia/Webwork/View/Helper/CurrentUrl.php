<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class CurrentUrl extends Helper
{
    /**
     * Caller
     *
     * @return string
     */
    public function currentUrl()
    {
        $app = $this->getView()
            ->getApp();

        $router = $app->getRouter();
        $dispatcher = $app->getDispatcher();

        $name = $dispatcher->getRoute()
            ->getName();

        $params = $app->getRequest()
                ->getUriParams();

        $url = $router->assemble($name, $params);

        return $url;
    }
}
