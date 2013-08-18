<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Date extends Helper
{
    /**
     * Caller
     *
     * @param \DateTime|mixed $value  Unix timestamp or string, e.g '+ 1 week'
     * @param string          $format Date format
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function date($value = null, $format = '%Y-%m-%d %H:%M:%S')
    {
        if (Value::isEmpty($value)) {
            return null;
        }

        $value = Value::getDate($value);
        if ($value === null) {
            return null;
        }

        $result = strftime($format, $value->getTimestamp());

        return $result;
    }
}
