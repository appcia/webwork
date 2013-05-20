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
     * Services and parameters
     *
     * @var Config
     */
    private $values;

    /**
     * Constructor
     *
     * @param Config $config Data container
     */
    public function __construct(Config $config = null)
    {
        if ($config === null) {
            $config = new Config();
        }

        $this->values = $config;
    }

    /**
     * Set a parameter or a service
     *
     * @param string $id    Unique identifier
     * @param mixed  $value Parameter value or object as closure
     *
     * @return Container
     */
    public function set($id, $value)
    {
        $this->values[$id] = $value;

        return $this;
    }

    /**
     * Get a parameter or a service
     *
     * @param string $id Unique identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function get($id)
    {
        if (!$this->values->has($id)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        $isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');

        return $isFactory ? $this->values[$id]($this) : $this->values[$id];
    }

    /**
     * Get all keys
     *
     * @return array
     */
    public function keys()
    {
        return $this->values->keys();
    }

    /**
     * Check whether parameter or service is set
     *
     * @param string $id Unique identifier
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->values->has($id);
    }

    /**
     * Remove a parameter or a service
     *
     * @param string $id Unique identifier
     *
     * @return Container
     */
    public function remove($id)
    {
        unset($this->values[$id]);

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
     * @param string $id Unique identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function raw($id)
    {
        if (!$this->values->has($id)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->values[$id];
    }

    /**
     * Extend a service definition
     * Useful when extending an existing object closure without necessarily loading that object
     *
     * @param string   $id       Unique identifier
     * @param \Closure $callable Original closure replacement
     *
     * @return \Closure
     * @throws Exception
     */
    public function extend($id, \Closure $callable)
    {
        if (!$this->values->has($id)) {
            throw new Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        $factory = $this->values[$id];

        if (!($factory instanceof \Closure)) {
            throw new Exception(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }

        return $this->values[$id] = function ($container) use ($callable, $factory) {
            return $callable($factory($container), $container);
        };
    }

    /**
     * Store callable as unique
     *
     * @param string   $id       Unique identifier
     * @param callable $callable Closure
     *
     * @return Container
     */
    public function single($id, \Closure $callable)
    {
        $this->set($id, $this->share($callable));

        return $this;
    }
}