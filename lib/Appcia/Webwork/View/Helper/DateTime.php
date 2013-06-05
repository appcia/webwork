<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class DateTime extends Helper
{
    /**
     * Caller
     *
     * @param \DateTime|mixed $value  Unix timestamp or string, e.g '+ 1 week'
     * @param string          $format Date format, default is 'Y-m-d, H:i:s'
     *
     * @return string
     */
    public function dateTime($value = null, $format = 'Y-m-d, H:i:s')
    {
        $date = null;

        try {
            $date = new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }

        $result = $date->format($format);

        return $result;
    }
}
