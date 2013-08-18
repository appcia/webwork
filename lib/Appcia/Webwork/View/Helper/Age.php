<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Age extends Helper
{
    /**
     * Caller
     *
     * @param mixed $date Unix timestamp or string, e.g '+ 1 week'
     *
     * @return string
     */
    public function age($date)
    {
        if (Value::isEmpty($date)) {
            return null;
        }

        $date = Value::getDate($date);
        $now = new \DateTime('now');

        $age = $now->diff($date)->y;

        return $age;
    }
}
