<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Date extends Helper
{
    /**
     * Caller
     *
     * @param \DateTime|mixed $value  Unix timestamp or string, e.g '+ 1 week'
     * @param string          $format Date format, default is 'Y-m-d, H:i:s'
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function date($value = null, $format = 'Y-m-d, H:i:s')
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        $value = $this->getDateValue($value);

        if ($value === null) {
            return null;
        }

        $result = $value->format($format);

        return $result;
    }
}
