<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Render extends Helper
{
    /**
     * Caller
     *
     * @param string $file File to be rendered
     *
     * @return string
     */
    public function render($file)
    {
        return $this
            ->getView()
            ->render($file);
    }
}
