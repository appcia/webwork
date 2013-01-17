<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Date extends Helper
{
    /**
     * @var string
     */
    private $format;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->format = 'Y-m-d, H:i:s';
    }

    /**
     * Caller
     *
     * @param mixed  $value  Unix timestamp or string, e.g '+ 1 week'
     * @param string $format Date format, default is 'Y-m-d, H:i:s'
     *
     * @return string
     */
    public function date($value = null, $format = null)
    {
        if ($value === null) {
            $value = time();
        }

        if ($format === null) {
            $format = $this->format;
        }
        else {
            $format = (string) $format;
        }

        if (!is_int($value)) {
            $value = strtotime($value);
        }

        return date($format, $value);
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
