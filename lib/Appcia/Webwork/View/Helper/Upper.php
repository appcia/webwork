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
        $value = $this->getStringValue($value);
        if ($value === null) {
            return null;
        }

        $charset = $this->getContext()
            ->getCharset();

        $value = mb_strtoupper($value, $charset);

        return $value;
    }
}
