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

        if (!is_array($params)) {
            $params = array();
        }

        $url = $this->getView()
            ->getApp()
            ->getRouter()
            ->assemble($name, $params);

        return $url;
    }
}
