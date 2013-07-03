<?

namespace Appcia\Webwork\Storage;

use Appcia\Webwork\Data\Encoder;

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
     * Storage container
     *
     * @var \ArrayAccess
     */
    protected $storage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->encoder = new Encoder();
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
     * @param Encoder|string $encoder
     */
    public function setEncoder($encoder)
    {
        if (!$encoder instanceof Encoder) {
            $encoder = Encoder::create($encoder);
        }

        $this->encoder = $encoder;
    }

    /**
     * Service session by superglobal table
     *
     * @return void
     */
    public function loadGlobals()
    {
        session_start();
        $this->storage = & $_SESSION;
    }

    /**
     * Get storage
     *
     * @return \ArrayAccess
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set storage
     *
     * @param \ArrayAccess $storage
     *
     * @return $this
     */
    public function setStorage(\ArrayAccess $storage)
    {
        $this->storage = $storage;

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
        if (!isset($this->storage[$key])) {
            throw new \InvalidArgumentException(sprintf("Session key '%s' does not exist", $key));
        }

        $value = $this->storage[$key];

        if ($this->encoder !== null) {
            $value = $this->encoder->decode($value);
        }

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
        if ($this->encoder !== null) {
            $value = $this->encoder->encode($value);
        }

        $this->storage[$key] = $value;

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
        return isset($this->storage[$key]);
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
        unset($this->storage[$key]);

        return $this;
    }
}