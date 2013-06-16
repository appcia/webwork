<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Begin extends Helper
{
    /**
     * Caller
     *
     * @param string $name    Block name
     * @param string $file    View to be extended
     * @param string $module  Module name which in file belongs to
     *
     * @return void
     */
    public function begin($name, $file = null, $module = null)
    {
        $this->getView()
            ->getRenderer()
            ->beginBlock($name, $file, $module);
    }
}
