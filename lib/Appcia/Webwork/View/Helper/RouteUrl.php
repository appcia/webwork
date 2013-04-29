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
     * @return string|null
     */
    public function routeUrl($name = null, $params = null)
    {
        if ($name === null) {
            return null;
        }

        $container = $this
            ->getView()
            ->getContainer();

        if (!is_array($params)) {
            $params = array();
        }

        $router = $container->get('router');
        $url = $router->assemble($name, $params);

        return $url;
    }
}
