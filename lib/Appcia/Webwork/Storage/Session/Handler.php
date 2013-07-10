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
     * @return Handler
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        $handler = null;
        $type = null;
        $config = null;

        if ($data instanceof Config) {
            $data = $data->getData();
        }

        if (is_array($data)) {
            if (!isset($data['type'])) {
                throw new \InvalidArgumentException("Session handler config should contain key 'type'.");
            }

            $type = $data['type'];
            $config = new Config($data);
        } elseif (is_string($data)) {
            $type = $data;
        } else {
            throw new \InvalidArgumentException("Session handler data has invalid format.");
        }

        $class = $type;
        if (!class_exists($class)) {
            $class =  __CLASS__ . '\\' . ucfirst($type);
        }

        if (!class_exists($class) || !is_subclass_of($class, __CLASS__)) {
            throw new \InvalidArgumentException(sprintf("Session handler '%s' is invalid or unsupported.", $type));
        }

        $handler = new $class();

        if ($config !== null) {
            $config->inject($handler);
        }

        return $handler;
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