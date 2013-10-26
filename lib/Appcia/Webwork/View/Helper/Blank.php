<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Blank extends Helper
{
    /**
     * Caller
     *
     * @param mixed $value Value to be checked
     * @param mixed $arg1  Returned if value is empty and second argument is not specified
     * @param mixed $arg2  Returned if 2 arguments specified and value is empty
     *
     * @return mixed
     */
    public function blank($value, $arg1, $arg2 = null)
    {
        $value = Value::getString($value);
        if ($value === null) {
            if ($arg2 !== null) {
                return $arg2;
            } else {
                return $arg1;
            }
        }

        return $value;
    }
}
