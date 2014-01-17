<?

namespace Appcia\Webwork\Model\Mask;

use Appcia\Webwork\Model\Mask;
use Psr\Log\InvalidArgumentException;

/**
 * Mask with named options
 */
class Options extends Mask implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Mapped mask options to names
     *
     * @var array
     */
    protected $map;

    /**
     * Constructor
     *
     * @param array $map
     * @param int   $value
     */
    public function __construct(array $map = array(), $value = 0)
    {
        $this->setMap($map);
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
        foreach ($this->map as $option) {
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
            $option = array_search($name, $this->map);
            if ($option === false) {
                throw new \OutOfBoundsException(sprintf("Option by name '%s' not found.", $name));
            }
        } else {
            if (!array_key_exists($name, $this->map)) {
                throw new \OutOfBoundsException(sprintf("Option '%s' does not exist.", $name));
            }

            $option = $name;
        }

        return $option;
    }

    /**
     * Set mask options using names
     *
     * @param array $options Options
     *
     * @return $this
     */
    public function setAll(array $options)
    {
        foreach ($options as $option => $value) {
            $this->set($option, $value);
        }

        return $this;
    }

    /**
     * Enable or disable multiple options at once
     *
     * @param array   $options
     * @param boolean $flag
     *
     * @return $this
     */
    public function apply(array $options, $flag = true)
    {
        foreach ($options as $option) {
            $this->set($option, $flag);
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
     * Push various type value
     *
     * @param mixed $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function push($value)
    {
        if ($value instanceof self) {
            $value = $value->getValue();
        }

        if (is_numeric($value)) {
            $this->setValue($value);
        } elseif (is_array($value)) {
            $this->setAll($value);
        } else {
            throw new \InvalidArgumentException(sprintf(
                "Options value to be pushed has invalid type: '%s'.",
                gettype($value)
            ));
        }

        return $this;
    }

    /**
     * Get mask options
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set mask options
     *
     * @param array $options Mapped mask options to names
     *
     * @return $this
     */
    public function setMap($options)
    {
        $this->map = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($option)
    {
        return $this->has($option);
    }

    /**
     * Check whether option exist
     *
     * @param string $option Option name
     *
     * @return boolean
     */
    public function has($option)
    {
        return in_array($option, $this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($option)
    {
        return $this->is($option);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($option, $value)
    {
        $this->set($option, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($option)
    {
        $this->set($option, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }
}