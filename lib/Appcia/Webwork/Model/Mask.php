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
        $this->verify($value);
        $this->value = (int) $value;
    }

    /**
     * Verify value / option
     *
     * @param int $value Number
     *
     * @return Mask
     * @throws \InvalidArgumentException
     */
    private function verify($value) {
        if (!$this->check($value)) {
            throw new \InvalidArgumentException('Mask option value should a number which is a power of 2');
        }

        return $this;
    }

    /**
     * Check whether value is power of 2
     *
     * @param int $value Integer number
     *
     * @return bool
     */
    public static function check($value)
    {
        return ($value === 0) || (($value & ($value - 1)) == 0);
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

        if ($flag) {
            $this->mark($option);
        } else {
            $this->unmark($option);
        }

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
        if (!$this->check($option)) {
            return false;
        }

        return ($this->value & $option) > 0;
    }

    /**
     * Set mask option
     *
     * @param int $option Option value
     *
     * @return Mask
     */
    public function mark($option)
    {
        $this->verify($option);
        $this->value |= $option;

        return $this;
    }

    /**
     * Unset mask option
     *
     * @param int $option
     *
     * @return Mask
     */
    public function unmark($option)
    {
        $this->verify($option);
        $this->value &= ~$option;

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
     */
    public function setValue($value)
    {
        $this->verify($value);
        $this->value = $value;

        return $this;
    }
}