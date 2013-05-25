<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class FirstUpper extends Helper
{
    /**
     * Caller
     *
     * @param string $value Value
     *
     * @return string
     */
    public function firstUpper($value)
    {
        $value = ucfirst($value);

        return $value;
    }
}
