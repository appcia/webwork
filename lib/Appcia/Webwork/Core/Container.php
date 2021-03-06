<?

namespace Appcia\Webwork\Core;

use Appcia\Webwork\Storage\Config;

/**
 * DI container based on Pimple
 *
 * @link http://pimple.sensiolabs.org/
 */
class Container
{
    /**
     * Services and parameters
     *
     * @var Config
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = array();
    }

    /**
     * Get a parameter or a service
     *
     * @param string $key Key
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \OutOfBoundsException(sprintf("Container service or parameter '%s' does not exist.", $key));
        }

        $isFactory = is_object($this->values[$key]) && method_exists($this->values[$key], '__invoke');

        return $isFactory ? $this->values[$key]($this) : $this->values[$key];
    }

    /**
     * Get all keys
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->values);
    }

    /**
     * Check whether parameter or service is set
     *
     * @param string $key Key
     *
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Remove a parameter or a service
     *
     * @param string $key Key
     *
     * @return $this
     */
    public function remove($key)
    {
        unset($this->values[$key]);

        return $this;
    }

    /**
     * Protect callable from being interpreted as a service
     * Useful when storing a callable as a parameter
     *
     * @param \Closure $callable Closure to be protected from evaluation
     *
     * @return \Closure
     */
    public function protect(\Closure $callable)
    {
        return function ($container) use ($callable) {
            return $callable;
        };
    }

    /**
     * Get a parameter or a service
     *
     * @param string $key Key
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function raw($key)
    {
        if (!$this->values->has($key)) {
            throw new \OutOfBoundsException(sprintf("Container service or parameter '%s' does not exist.", $key));
        }

        return $this->values[$key];
    }

    /**
     * Extend a service definition
     * Useful when extending an existing object closure without necessarily loading that object
     *
     * @param string   $key      Key
     * @param \Closure $callable Original closure replacement
     *
     * @return \Closure
     * @throws \OutOfBoundsException
     * @throws \ErrorException
     */
    public function extend($key, \Closure $callable)
    {
        if (!$this->has($key)) {
            throw new \OutOfBoundsException(sprintf("Container service or parameter '%s' does not exist.", $key));
        }

        $factory = $this->values[$key];

        if (!($factory instanceof \Closure)) {
            throw new \ErrorException(sprintf("Container key '%s' does not contain an object definition.", $key));
        }

        return $this->values[$key] = function ($container) use ($callable, $factory) {
            return $callable($factory($container), $container);
        };
    }

    /**
     * Store callable as unique
     *
     * @param string   $key      Key
     * @param callable $callable Closure
     *
     * @return $this
     */
    public function single($key, \Closure $callable)
    {
        $this->set($key, $this->share($callable));

        return $this;
    }

    /**
     * Set a parameter or a service
     *
     * @param string $key   Key
     * @param mixed  $value Parameter value or object as closure
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * Get a closure that stores the result of the given closure for uniqueness
     *
     * @param \Closure $callable Closure for wrapping for uniqueness
     *
     * @return \Closure
     */
    public function share(\Closure $callable)
    {
        return function ($container) use ($callable) {
            static $object;

            if (null === $object) {
                $object = $callable($container);
            }

            return $object;
        };
    }
}