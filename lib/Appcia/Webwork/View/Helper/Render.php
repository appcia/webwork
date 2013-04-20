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
        $result = $this
            ->getView()
            ->render($file);

        return $result;
    }
}
