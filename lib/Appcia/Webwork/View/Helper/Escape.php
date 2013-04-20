<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Escape extends Helper
{
    /**
     * Caller
     *
     * @param string $value Value to be filtered
     *
     * @return string
     */
    public function escape($value)
    {
        $charset = $this->getContext()
            ->getCharset();

        return htmlentities($value, ENT_QUOTES, $charset);
    }
}
