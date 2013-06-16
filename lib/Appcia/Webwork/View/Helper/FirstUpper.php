<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class FirstUpper extends Helper
{
    /**
     * Caller
     *
     * @param string  $value     Value
     * @param boolean $lowerRest Lowercase all except first letter
     *
     * @return string
     */
    public function firstUpper($value, $lowerRest = false)
    {
        $value = $this->getStringValue($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $first = mb_strtoupper(mb_substr($value, 0, 1, $charset), $charset);
        $rest = null;

        if ($lowerRest) {
            $rest = mb_strtolower(mb_substr($value, 1, mb_strlen($value, $charset), $charset), $charset);
        } else {
            $rest = mb_substr($value, 1, mb_strlen($value, $charset), $charset);
        }

        $result = $first . $rest;

        return $result;
    }
}
