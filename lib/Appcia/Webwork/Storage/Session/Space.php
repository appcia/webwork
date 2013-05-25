<?

namespace Appcia\Webwork\Storage\Session;

use Appcia\Webwork\Storage\Session;

/**
 * Correlates data into one namespace in session storage
 *
 * @package Appcia\Webwork\Storage\Session
 */
class Space implements \ArrayAccess
{
    /**
     * Session storage
     *
     * @var Session
     */
    private $session;

    /**
     * Name (session key)
     *
     * @var string
     */
    private $name;

    /**
     * Stored data
     *
     * @var array
     */
    private $data;

    /**
     * Flush when data is dirty
     *
     * @var bool
     */
    private $autoflush;

    /**
     * Data dirty flag
     *
     * @var bool
     */
    private $clean;

    /**
     * Constructor
     *
     * @param Session $session   Session
     * @param string  $name      Namespace
     * @param bool    $autoflush Flush when data is dirty
     */
    public function __construct(Session $session, $name, $autoflush = true)
    {
        $this->session = $session;
        $this->name = $name;
        $this->autoflush = $autoflush;

        $this->clean = false;
        $this->data = array();
    }

    /**
     * Get session storage
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get name (session key)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get actual data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get actual keys
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Flush when data is dirty
     *
     * @return bool
     */
    public function isAutoflush()
    {
        return $this->autoflush;
    }

    /**
     * Set automatic saving / loading when data is dirty
     *
     * @param bool $flag Automatic flush
     *
     * @return Space
     */
    public function setAutoflush($flag)
    {
        $this->autoflush = $flag;

        return $this;
    }

    /**
     * Data clean check
     *
     * @return boolean
     */
    public function isClean()
    {
        return $this->clean;
    }

    /**
     * Force data state
     *
     * @param bool $flag
     *
     * @return Space
     */
    public function setClean($flag)
    {
        $this->clean = $flag;

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
     * Check that value is set
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (!$this->clean && $this->autoflush) {
            $this->load();
        }

        return array_key_exists($key, $this->data);
    }

    /**
     * Load data from session
     *
     * @return Space
     */
    public function load()
    {
        if ($this->session->has($this->name)) {
            $this->data = $this->session->get($this->name);
        }

        $this->clean = true;

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
     * Get a value
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->clean && $this->autoflush) {
            $this->load();
        }

        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
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
     * Set a value
     *
     * @param string $key   Key
     * @param string $value Value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (!$this->clean && $this->autoflush) {
            $this->load();
        }

        if (!array_key_exists($key, $this->data) || $value !== $this->data[$key]) {
            $this->clean = false;
            $this->data[$key] = $value;
        }

        if ($this->autoflush) {
            $this->save();
        }

        return $this;
    }

    /**
     * Save data in session
     *
     * @return Session
     */
    public function save()
    {
        $this->session->set($this->name, $this->data);
        $this->clean = true;

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
     * Remove a value
     *
     * @param string $key Key
     *
     * @return $this
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
            $this->clean = false;

            if ($this->autoflush) {
                $this->save();
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $key = key($this->data);

        return ($key !== null) && ($key !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        return reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }
}