<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Begin extends Helper
{
    /**
     * Caller
     *
     * @param string $name Block name
     * @param string $file View to be extended
     *
     * @return void
     */
    public function begin($name, $file = null)
    {
        $this->getHelper('block')
            ->begin($name, $file);
    }
}
