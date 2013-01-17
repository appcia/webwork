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
    public function routeUrl($name, array $params = array())
    {
        $container = $this
            ->getView()
            ->getContainer();

        $router = $container['router'];

        return $router->assemble($name, $params);
    }
}
