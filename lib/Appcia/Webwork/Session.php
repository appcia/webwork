<?

namespace Appcia\Webwork;

class Session
{
    /**
     * Storage container
     *
     * @var \ArrayAccess
     */
    private $storage;

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
     * Set storage
     *
     * @param \ArrayAccess $storage
     *
     * @return Session
     */
    public function setStorage(\ArrayAccess $storage)
    {
        $this->storage = $storage;

        return $this;
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
     * Get stored value by key
     *
     * @param $key Key
     *
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        if (!isset($this->storage[$key])) {
            throw new Exception('Specified value does not exist');
        }

        return unserialize($this->storage[$key]);
    }

    /**
     * Set value in storage
     *
     * @param $name
     * @param $value
     *
     * @return Session
     */
    public function set($name, $value)
    {
        $this->storage[$name] = serialize($value);

        return $this;
    }

    /**
     * Check whether value in storage exists
     *
     * @param string $key Key
     *
     * @return bool
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
     * @return Session
     */
    public function clear($key)
    {
        $this->storage[$key] = array();

        return $this;
    }
}