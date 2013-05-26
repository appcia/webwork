<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Block extends Helper
{
    /**
     * Caller
     *
     * @param string $name Name
     *
     * @return void
     */
    public function block($name)
    {
        echo $this->getView()
            ->getRenderer()
            ->getBlock($name);
    }
}
