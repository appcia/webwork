<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class BaseUrl extends Helper
{
    /**
     * Caller
     *
     * @return string
     */
    public function baseUrl()
    {
        return trim($this
            ->getView()
            ->getGlobal('baseUrl'), '/');
    }
}
