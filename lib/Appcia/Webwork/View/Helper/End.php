<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class End extends Helper
{
    /**
     * Caller
     *
     * @param string $name Block name
     *
     * @return void
     */
    public function end($name = null)
    {
        $this->getHelper('block')
            ->end($name);
    }
}
