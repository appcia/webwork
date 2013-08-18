<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Trim extends Helper
{
    /**
     * Caller
     *
     * @param mixed $value Data
     *
     * @return mixed
     */
    public function trim($value)
    {
        $value = Value::getString($value);
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value;
    }
}
