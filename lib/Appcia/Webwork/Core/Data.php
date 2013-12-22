<?

namespace Appcia\Webwork\Core;

class Data implements \IteratorAggregate, \ArrayAccess
{
    /**
     * Data
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->setData($data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Set data
     *
     * @param array $data Data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = (array) $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Check value by key
     *
     * @param mixed $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);

        return $this;
    }

    /**
     * Set value using key
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);

        return $this;
    }

    /**
     * Remove value by key
     *
     * @param mixed $key
     *
     * @return $this
     */
    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Get value by key
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $data = $this->has($key)
            ? $this->data[$key]
            : $default;

        if (is_array($data)) {
            return new self($data);
        } else {
            return $data;
        }
    }

    /**
     * Allows iterating with foreach loop
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @see get()
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @see set()
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
}