<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Lower extends Helper
{
    /**
     * Caller
     *
     * @param string $value Value to be filtered
     *
     * @return string
     */
    public function lower($value)
    {
        $charset = $this
            ->getView()
            ->getGlobal('charset');

        return mb_strtolower($value, $charset);
    }
}
