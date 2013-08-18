<?

namespace Appcia\Webwork\View\Helper;

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
        if ($this->isEmptyValue($value)) {
            return null;
        }

        $value = $this->getDateValue($value);

        if ($value === null) {
            return null;
        }

        $result = strftime($format, $value);

        return $result;
    }
}
