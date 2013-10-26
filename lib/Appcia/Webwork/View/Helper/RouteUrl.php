<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
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
    public function routeUrl($name = null, $params = array())
    {
        $name = Value::getString($name);
        if ($name === null) {
            return null;
        }

        $url = $this->getView()
            ->getApp()
            ->getRouter()
            ->assemble($name, (array) $params);

        return $url;
    }
}
