<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class FirstLower extends Helper
{
    /**
     * Caller
     *
     * @param string  $value     Value
     * @param boolean $upperRest Uppercase all except first letter
     *
     * @return string
     */
    public function firstLower($value, $upperRest = false)
    {
        $value = Value::getString($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $first = mb_strtolower(mb_substr($value, 0, 1, $charset), $charset);
        $rest = null;

        if ($upperRest) {
            $rest = mb_strtoupper(mb_substr($value, 1, mb_strlen($value, $charset), $charset), $charset);
        } else {
            $rest = mb_substr($value, 1, mb_strlen($value, $charset), $charset);
        }

        $result = $first . $rest;

        return $result;
    }
}
