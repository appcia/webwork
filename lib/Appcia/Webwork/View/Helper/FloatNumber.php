<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class FloatNumber extends Helper
{
    /**
     * @var int
     */
    private $decimals;

    /**
     * @var string
     */
    private $decimalPoint;

    /**
     * @var string
     */
    private $thousandsSeparator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decimals = 2;
        $this->decimalPoint = ',';
        $this->thousandsSeparator = '.';
    }

    /**
     * Caller
     *
     * @param float  $number             Value to be treated as float number
     * @param int    $decimals           Decimal digits count after comma
     *
     * @return string
     */
    public function floatNumber($number, $decimals = null)
    {
        if ($decimals === null) {
            $decimals = $this->decimals;
        }

        return number_format(floatval($number), (int) $decimals, $this->decimalPoint, $this->thousandsSeparator);
    }

    /**
     * Set decimal point character
     *
     * @param string $decimalPoint
     */
    public function setDecimalPoint($decimalPoint)
    {
        $this->decimalPoint = (string) $decimalPoint;
    }

    /**
     * Get decimal point character
     *
     * @return string
     */
    public function getDecimalPoint()
    {
        return $this->decimalPoint;
    }

    /**
     * Specify digit count after decimal point
     *
     * @param int $decimals Digit count
     *
     * @return Number
     */
    public function setDecimals($decimals)
    {
        $this->decimals = (int) $decimals;

        return $this;
    }

    /**
     * Get digit count after decimal point
     *
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * Set thousands separator
     *
     * @param string $thousandsSeparator
     */
    public function setThousandsSeparator($thousandsSeparator)
    {
        $this->thousandsSeparator = (string) $thousandsSeparator;
    }

    /**
     * Get thousands separator
     *
     * @return string
     */
    public function getThousandsSeparator()
    {
        return $this->thousandsSeparator;
    }
}
