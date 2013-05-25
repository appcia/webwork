<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Replace extends Helper
{
    /**
     * Caller
     *
     * @param string $search  Search
     * @param string $replace Replace
     * @param string $subject Subject
     *
     * @return string
     */
    public function replace($subject, $search, $replace)
    {
        $value = str_replace($search, $replace, $subject);

        return $value;
    }
}
