<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Get extends Helper
{
    /**
     * Caller
     *
     * @param string $key Container key
     *
     * @return mixed
     */
    public function get($key)
    {
        $service = $this->getView()
            ->getApp()
            ->getContainer()
            ->get($key);

        return $service;
    }
}
