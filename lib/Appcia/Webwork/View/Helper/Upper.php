<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Upper extends Helper
{
    /**
     * Caller
     *
     * @param string $value Value to be filtered
     *
     * @return string
     */
    public function upper($value)
    {
        $value = Value::getString($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $value = mb_strtoupper($value, $charset);

        return $value;
    }
}
