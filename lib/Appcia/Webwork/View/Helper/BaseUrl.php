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
        return $this->context->getBaseUrl();
    }
}
