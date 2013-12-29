<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Control\Lite;
use Appcia\Webwork\View\Helper;

class Control extends Helper
{
    /**
     * Caller
     *
     * @return Lite
     */
    public function control()
    {
        $control = $this->getView()
            ->getApp()
            ->getDispatcher()
            ->getControl();

        return $control;
    }
}
