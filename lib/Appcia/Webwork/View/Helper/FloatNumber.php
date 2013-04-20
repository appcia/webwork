<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class FloatNumber extends Helper
{
    /**
     * Caller
     *
     * @param float  $number       Value to be treated as float number
     * @param int    $decimals     Decimal digits count after comma
     * @param string $decPoint     Decimal point
     * @param string $thousandSep Thousands separator
     *
     * @return string
     */
    public function floatNumber($number, $decimals = null, $decPoint = '.', $thousandSep = ',')
    {
        $number = number_format(floatval($number), (int) $decimals, $decPoint, $thousandSep);

        return $number;
    }
}
