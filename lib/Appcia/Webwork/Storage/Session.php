<?

namespace Appcia\Webwork\Storage;

use Appcia\Webwork\Data\Encoder;
use Appcia\Webwork\Storage\Session\Handler;

/**
 * Session representation
 *
 * @package Appcia\Webwork\Storage
 */
class Session
{
    /**
     * Data serializer
     */
    protected $encoder;

    /**
     * Storage handler
     *
     * @var Handler
     */
    protected $handler;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->encoder = new Encoder();
        $this->handler = new Handler\Php();
    }

    /**
     * Get data encoder
     *
     * @return Encoder|null
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * Set data encoder
     *
     * @param Encoder $encoder
     *
     * @return $this
     */
    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Get data handler
     *
     * @return Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set data handler
     *
     * @param mixed $handler
     *
     * @return $this
     */
    public function setHandler($handler)
    {
        if (!$handler instanceof Handler) {
            $handler = Handler::objectify($handler);
        }

        $this->handler = $handler;

        return $this;
    }

    /**
     * Get stored value by key
     *
     * @param string $key Key
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key)
    {
        if (!isset($this->handler[$key])) {
            throw new \InvalidArgumentException(sprintf("Session key '%s' does not exist", $key));
        }

        $value = $this->load($key);

        return $value;
    }

    /**
     * Grab stored value by key
     *
     * @param string $key     Key
     * @param mixed  $default Default value
     *
     * @return mixed|null
     */
    public function grab($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        $value = $this->load($key);

        return $value;
    }

    /**
     * Set value in storage
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->save($key, $value);

        return $this;
    }

    /**
     * Check whether value in storage exists
     *
     * @param string $key Key
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->handler[$key]);
    }

    /**
     * Clear all values by key
     *
     * @param string $key Key
     *
     * @return $this
     */
    public function clear($key)
    {
        unset($this->handler[$key]);

        return $this;
    }

    /**
     * Save value using handler
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return $this
     */
    protected function save($key, $value)
    {
        if ($this->encoder !== null) {
            $value = $this->encoder->encode($value);
        }

        $this->handler[$key] = $value;

        return $this;
    }

    /**
     * Load value using handler
     *
     * @param string $key Key
     *
     * @return mixed
     */
    protected function load($key)
    {
        $value = $this->handler[$key];

        if ($this->encoder !== null) {
            $value = $this->encoder->decode($value);

            return $value;
        }

        return $value;
    }
}