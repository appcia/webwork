<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Number extends Helper
{
    /**
     * Caller
     *
     * @param float  $number Value to be treated as integer number
     *
     * @return string
     */
    public function number($number)
    {
        return intval($number);
    }
}
