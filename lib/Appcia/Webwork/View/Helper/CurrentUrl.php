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
        $container = $this
            ->getView()
            ->getContainer();

        $router = $container->get('router');
        $dispatcher = $container->get('dispatcher');

        $name = $dispatcher->getRoute()
            ->getName();

        $params = array_merge(
            $dispatcher->getRequest()
                ->getGet(),
            $dispatcher->getRequest()
                ->getParams()
        );

        $url = $router->assemble($name, $params);

        return $url;
    }
}
