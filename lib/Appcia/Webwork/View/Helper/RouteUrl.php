<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class RouteUrl extends Helper
{
    /**
     * Caller
     *
     * @param string $name   Route name
     * @param array  $params Route params
     *
     * @return string
     */
    public function routeUrl($name = null, array $params = array())
    {
        $container = $this
            ->getView()
            ->getContainer();

        $router = $container->get('router');
        $dispatcher = $container->get('dispatcher');

        if ($name === null) {
            $name = $dispatcher->getRoute()
                ->getName();

            $params = $dispatcher->getRequest()
                ->getParams();
        }

        return $router->assemble($name, $params);
    }
}
