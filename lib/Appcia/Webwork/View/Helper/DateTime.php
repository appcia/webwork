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
     * @throws \InvalidArgumentException
     */
    public function dateTime($value = null, $format = 'Y-m-d, H:i:s')
    {
        if (empty($value)) {
            return null;
        } elseif (!$value instanceof \DateTime) {
            if ($this->isStringifyable($value)) {
                $value = (string) $value;
            } else {
                return null;
            }

            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        $result = $value->format($format);

        return $result;
    }
}
