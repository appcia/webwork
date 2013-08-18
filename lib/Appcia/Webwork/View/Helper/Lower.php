<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
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
        $value = Value::getString($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $value = mb_strtolower($value, $charset);

        return $value;
    }
}
