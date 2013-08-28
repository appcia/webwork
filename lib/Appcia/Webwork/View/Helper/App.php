<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class App extends Helper
{
    /**
     * Caller
     *
     * @return mixed
     */
    public function app()
    {
        $service = $this->getView()
            ->getApp();

        return $service;
    }
}
