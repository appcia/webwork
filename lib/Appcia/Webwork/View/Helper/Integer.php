<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Integer extends Helper
{
    /**
     * Caller
     *
     * @param float $number Value to be treated as integer number
     *
     * @return string
     */
    public function integer($number)
    {
        $number = intval($number);

        return $number;
    }
}
