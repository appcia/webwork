<?

namespace Appcia\Webwork\Model;

/**
 * Wrapper for number which is power of 2
 *
 * Useful for optimized options (storing in database as one integer column etc...)
 *
 * @package Appcia\Webwork\Model
 */
class Mask
{
    /**
     * Plain integer value
     *
     * @var int
     */
    private $value;

    /**
     * Constructor
     *
     * @param int $value
     */
    public function __construct($value = 0)
    {
        $this->setValue($value);
    }

    /**
     * Check whether value is power of 2
     *
     * @param int $option Integer number
     *
     * @return bool
     */
    public static function checkOption($option)
    {
        return ($option === 0) || (($option & ($option - 1)) == 0);
    }

    /**
     * Check whether value could be a mask
     *
     * @param $value
     *
     * @return bool
     */
    public static function checkValue($value)
    {
        return $value >= 0;
    }

    /**
     * Toggle mask option
     *
     * @param int  $option Option value
     * @param bool $flag   Flag
     *
     * @return Mask
     */
    public function toggle($option, $flag = null)
    {
        if ($flag === null) {
            $flag = !$this->is($option);
        }

        $this->set($option, $flag);

        return $this;
    }

    /**
     * Check some mask option
     *
     * @param int $option Option value
     *
     * @return bool
     */
    public function is($option)
    {
        if (!$this->checkOption($option)) {
            return false;
        }

        $flag = ($this->value & $option) > 0;

        return $flag;
    }

    /**
     * Set mask option
     *
     * @param int  $option Option value
     * @param bool $flag   True of false
     *
     * @return Mask
     * @throws \InvalidArgumentException
     */
    public function set($option, $flag)
    {
        if (!$this->checkOption($option)) {
            throw new \InvalidArgumentException(sprintf(
                "Mask option should be a number which is power of 2.", $option
            ));
        }

        if ($flag) {
            $this->value |= $option;
        } else {
            $this->value &= ~$option;
        }

        return $this;
    }

    /**
     * Get integer value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set integer value
     *
     * @param int $value Value
     *
     * @return Mask
     * @throws \InvalidArgumentException
     */
    public function setValue($value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Mask value should be greater than 0.");
        }

        $this->value = $value;

        return $this;
    }
}