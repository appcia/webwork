<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class FirstUpper extends Helper
{
    /**
     * Caller
     *
     * @param string $str Value to be parsed
     *
     * @return string
     */
    public function firstUpper($str)
    {
        $charset = $this->getContext()
            ->getCharset();

        $letter = mb_strtoupper(mb_substr($str, 0, 1, $charset), $charset);
        $str = $letter . mb_strtolower(mb_substr($str, 1, mb_strlen($str, $charset), $charset), $charset);

        return $str;
    }
}
