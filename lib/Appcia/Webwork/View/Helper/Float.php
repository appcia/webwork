<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Float extends Helper
{
    /**
     * Caller
     *
     * @param float  $number      Value to be treated as float number
     * @param int    $decimals    Decimal digits count after comma
     * @param string $decPoint    Decimal point
     * @param string $thousandSep Thousands separator
     *
     * @return string
     */
    public function float($number, $decimals = null, $decPoint = '.', $thousandSep = ',')
    {
        $number = floatval($number);
        $number = number_format($number, (int) $decimals, $decPoint, $thousandSep);

        return $number;
    }
}
