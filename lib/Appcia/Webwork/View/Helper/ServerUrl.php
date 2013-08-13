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
        $url = $this->getView()
            ->getApp()
            ->getRequest()
            ->getServerUrl();

        return $url;
    }
}
