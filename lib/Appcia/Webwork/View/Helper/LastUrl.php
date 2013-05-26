<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class LastUrl extends Helper
{
    /**
     * Caller
     *
     * @param bool $different Last URL that differs to current
     *
     * @return string
     */
    public function lastUrl($different = true)
    {
        $tracker = $this->getView()
            ->getApp()
            ->get('tracker');

        $url = $different ? $tracker->getPreviousUrl() : $tracker->getLastUrl();

        return $url;
    }
}
