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
        $value = $this->getStringValue($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $value = htmlentities($value, ENT_QUOTES, $charset);

        return $value;
    }
}
