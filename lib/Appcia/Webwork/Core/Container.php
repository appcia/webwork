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
     * Service or parameter container
     *
     * @var Container
     */
    private static $instance;

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
     * Setup instance
     *
     * @param Container $container
     *
     * @return void
     */
    public static function setup($container = null)
    {
        if ($container === null) {
            $container = new self();
        }

        self::$instance = $container;
    }

    /**
     * Shortcut getter
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public static function acquire($key)
    {
        return self::instance()->get($key);
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
        if (!$this->values->has($key)) {
            throw new \OutOfBoundsException(sprintf("Container key '%s' does not exist.", $key));
        }

        $isFactory = is_object($this->values[$key]) && method_exists($this->values[$key], '__invoke');

        return $isFactory ? $this->values[$key]($this) : $this->values[$key];
    }

    /**
     * Get instance
     *
     * @return Container
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Get all keys
     *
     * @return array
     */
    public function keys()
    {
        return $this->values->getKeys();
    }

    /**
     * Check whether parameter or service is set
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->values->has($key);
    }

    /**
     * Remove a parameter or a service
     *
     * @param string $key Key
     *
     * @return Container
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
            throw new \OutOfBoundsException(sprintf("Container key '%s' does not exist.", $key));
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
        if (!$this->values->has($key)) {
            throw new \OutOfBoundsException(sprintf("Container key '%s' does not exist.", $key));
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
     * @return Container
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
     * @return Container
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