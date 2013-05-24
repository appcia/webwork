<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class ServerUrl extends Helper
{
    /**
     * Caller
     *
     * @return string
     */
    public function serverUrl()
    {
        $request = $this->getView()
            ->getApp()
            ->getRequest();

        $protocol = $request->getProtocolPrefix();
        $server = trim($request->getServer(), '/');

        $url = $protocol . $server;

        return $url;
    }
}
