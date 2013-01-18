<?

namespace Appcia\Webwork;

require_once __DIR__ .'/../../../vendor/pimple.php';

class Container extends \Pimple implements \Iterator
{
    /**
     * Wrapper for offset get
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function get($key) {
        return $this->offsetGet($key);
    }

    /**
     * Wrapper for offset set
     *
     * @param string   $key   Key
     * @param \Closure $value Value
     *
     * @return void
     */
    public function set($key, \Closure $value) {
        $this->offsetSet($key, $value);
    }

    /**
     * Store callable as unique
     *
     * @param string   $key      Key
     * @param callable $callable Closure
     */
    public function single($key, \Closure $callable) {
        $this->offsetSet($key, $this->share($callable));
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