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
        $charset = $this->getContext()
            ->getCharset();

        $first = mb_substr(mb_strtoupper($value, $charset), 0, 1, $charset);
        $result = $first . mb_substr(mb_strtolower($value, $charset), 1, mb_strlen($value), $charset);

        return $result;
    }
}
