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
     */
    public function date($value = null, $format = null)
    {
        if ($value === null) {
            $value = new \DateTime();
        }

        if ($format === null) {
            $format = 'Y-m-d, H:i:s';
        } else {
            $format = (string) $format;
        }

        if (!$value instanceof \DateTime) {
            $value = new \DateTime($value);
        }

        $date = $value->format($format);

        return $date;
    }
}
