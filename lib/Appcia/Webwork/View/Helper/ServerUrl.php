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
        $container = $this
            ->getView()
            ->getContainer();

        $request = $container['request'];

        $protocol = $request->getProtocolPrefix();
        $server = trim($request->getServer(), '/');

        return $protocol . $server;
    }
}
