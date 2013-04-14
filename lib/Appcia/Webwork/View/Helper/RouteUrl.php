<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class RouteUrl extends Helper
{
    /**
     * Caller
     *
     * @param string     $name   Route name
     * @param null|array $params Route params
     *
     * @return string
     */
    public function routeUrl($name = null, $params = null)
    {
        $container = $this
            ->getView()
            ->getContainer();

        $router = $container->get('router');
        $dispatcher = $container->get('dispatcher');

        if ($name === null) {
            $name = $dispatcher->getRoute()
                ->getName();
        }

        if ($params === null) {
            $params = array_merge(
                $dispatcher->getRequest()
                    ->getGet(),
                $dispatcher->getRequest()
                    ->getParams()
            );
        } else if (!is_array($params)) {
            $params = array();
        }

        return $router->assemble($name, $params);
    }
}
