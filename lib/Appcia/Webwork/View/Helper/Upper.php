<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Upper extends Helper
{
    /**
     * Caller
     *
     * @param string $value Value to be filtered
     *
     * @return string
     */
    public function upper($value)
    {
        $charset = $this->getContext()
            ->getCharset();

        $value = mb_strtoupper($value, $charset);

        return $value;
    }
}
