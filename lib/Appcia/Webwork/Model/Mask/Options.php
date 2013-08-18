<?

namespace Appcia\Webwork\Model\Mask;

use Appcia\Webwork\Model\Mask;

/**
 * Mask with named options
 */
class Options extends Mask
{
    /**
     * Mapped mask options to names
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param int   $value
     * @param array $options
     */
    public function __construct(array $options = array(), $value = 0)
    {
        $this->setOptions($options);
        parent::__construct($value);
    }

    /**
     * Get all options with names
     *
     * @return array
     */
    public function getAll()
    {
        $values = array();
        foreach ($this->options as $option) {
            $values[$option] = $this->is($option);
        }

        return $values;
    }

    /**
     * Check option by name or integer
     *
     * @param string|int $option
     *
     * @return bool
     */
    public function is($option)
    {
        $option = $this->map($option);

        return parent::is($option);
    }

    /**
     * Map option name to integer
     *
     * @param string|int $name Option
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function map($name)
    {
        $option = null;
        if (is_string($name)) {
            $option = array_search($name, $this->options);
            if ($option === false) {
                throw new \OutOfBoundsException(sprintf("Option by name '%s' not found.", $name));
            }
        } else {
            if (!array_key_exists($name, $this->options)) {
                throw new \OutOfBoundsException(sprintf("Option '%s' does not exist.", $name));
            }

            $option = $name;
        }

        return $option;
    }

    /**
     * Set mask options using names
     *
     * @param array $values
     *
     * @return $this
     */
    public function setAll(array $values)
    {
        foreach ($values as $option => $value) {
            $this->set($option, $value);
        }

        return $this;
    }

    /**
     * Set option by name or integer
     *
     * @param string|int $option
     * @param boolean    $flag
     *
     * @return $this
     */
    public function set($option, $flag)
    {
        $option = $this->map($option);

        return parent::set($option, $flag);
    }

    /**
     * Get mask options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set mask options
     *
     * @param array $options Mapped mask options to names
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}