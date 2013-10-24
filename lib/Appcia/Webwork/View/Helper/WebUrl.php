<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;
use Appcia\Webwork\Web\Context;

class WebUrl extends Helper
{
    /**
     * Caller
     *
     * @param string $path
     *
     * @return string
     */
    public function webUrl($path)
    {
        if (Value::isEmpty($path)) {
            return null;
        }

        $url = $this->context->getBaseUrl();
        if (!empty($url)) {
            $url .= '/';
        }
        $url .= 'web/' . $path;

        return $url;
    }
}
