<?

namespace Appcia\Webwork;

/**
 * Pimple based DI container
 *
 * @link http://pimple.sensiolabs.org/
 */
class Container
{
    /**
     * @var array
     */
    private $values;

    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    public function __construct (array $values = array())
    {
        $this->values = $values;
    }

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to defined an object
     */
    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     * @throws Exception if the identifier is not defined
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        $isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');

        return $isFactory ? $this->values[$id]($this) : $this->values[$id];
    }

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->values);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function has($id)
    {
        return array_key_exists($id, $this->values);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     */
    public function remove($id)
    {
        unset($this->values[$id]);
    }

    /**
     * Returns a closure that stores the result of the given closure for
     * uniqueness in the scope of this instance of Pimple.
     *
     * @param \Closure $callable A closure to wrap for uniqueness
     *
     * @return \Closure The wrapped closure
     */
    public function share(\Closure $callable)
    {
        return function ($c) use ($callable) {
            static $object;

            if (null === $object) {
                $object = $callable($c);
            }

            return $object;
        };
    }

    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param \Closure $callable A closure to protect from being evaluated
     *
     * @return \Closure The protected closure
     */
    public function protect(\Closure $callable)
    {
        return function ($c) use ($callable) {
            return $callable;
        };
    }

    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws Exception if the identifier is not defined
     */
    public function raw($id)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->values[$id];
    }

    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string  $id       The unique identifier for the object
     * @param \Closure $callable A closure to extend the original
     *
     * @return \Closure The wrapped closure
     *
     * @throws Exception if the identifier is not defined
     */
    public function extend($id, \Closure $callable)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        $factory = $this->values[$id];

        if (!($factory instanceof \Closure)) {
            throw new Exception(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }

        return $this->values[$id] = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
    }

    /**
     * Store callable as unique
     *
     * @param string   $key      Key
     * @param callable $callable Closure
     */
    public function single($key, \Closure $callable) {
        $this->set($key, $this->share($callable));
    }
}