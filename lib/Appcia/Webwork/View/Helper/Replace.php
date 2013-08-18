<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Replace extends Helper
{
    /**
     * Caller
     *
     * @param string $search  Search
     * @param string $replace Replace
     * @param string $value   Subject
     *
     * @return string
     */
    public function replace($value, $search, $replace)
    {
        $value = Value::getString($value);
        if ($value === null) {
            return null;
        }

        $value = str_replace($search, $replace, $value);

        return $value;
    }
}
