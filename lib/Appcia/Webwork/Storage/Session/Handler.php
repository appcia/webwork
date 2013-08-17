<?

namespace Appcia\Webwork\Storage\Session;

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Session;

/**
 * Session data handler
 *
 * @package Appcia\Webwork\Storage\Session
 */
abstract class Handler implements \ArrayAccess
{
    /**
     * Stored data
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = array();
    }

    /**
     * Creator
     *
     * @param mixed $data Config data
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        return Config::create($data, get_called_class());
    }

    /**
     * Get stored data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data to store
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);

        return $this;
    }
}