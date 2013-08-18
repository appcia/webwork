<?

namespace Appcia\Webwork\Core;

/**
 * Object configurator
 *
 * @package Appcia\Webwork\Core
 */
class Objector
{
    /**
     * Manipulation data
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param array $data Data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data Data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        // Deterministic injecting
        ksort($data);

        $this->data = $data;

        return $this;
    }

    /**
     * Inject data by object setters
     *
     * @param object $object Target object
     *
     * @return $this
     */
    public function inject($object)
    {
        foreach ($this->data as $property => $value) {
            foreach (array('add', 'set') as $prefix) {
                $method = $prefix . ucfirst($property);
                $callback = array($object, $method);

                if (method_exists($object, $method) && is_callable($callback)) {
                    call_user_func($callback, $value);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Suck data from object using getters
     *
     * @param object     $object     Source object
     * @param array|null $properties Properties to be retrieved
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function suck($object, $properties = null)
    {
        if ($properties === null) {
            $properties = get_object_vars($object);
        } elseif (!is_array($properties)) {
            throw new \InvalidArgumentException('Object property names should be passed as an array.');
        }

        foreach ($properties as $property) {
            foreach (array('get', 'is') as $prefix) {
                $method = $prefix . ucfirst($property);
                $callback = array($object, $method);

                if (method_exists($object, $method) && is_callable($callback)) {
                    $value = call_user_func($callback);

                    if ($value !== null) {
                        $this->data[$property] = $value;
                    }

                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Create object by definition from configuration
     *
     * @param string|array $base Base class name
     * @param array        $args Constructor arguments
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function instantiate($base = null, $args = array())
    {
        $name = null;
        if (isset($this->data['class'])) {
            $name = ucfirst($this->data['class']);
        } else {
            if ($base !== null) {
                $name = $base;
            } else {
                throw new \InvalidArgumentException("Object instantiation requires key 'class' specified.");
            }
        }

        $class = $name;
        $namespaces = array();
        if ($base !== null) {
            $namespaces[] = $base;
        }

        if (!class_exists($class)) {
            if (!empty($this->data['namespace'])) {
                $namespaces = array_merge($namespaces, $this->data['namespace']);
            }

            $found = false;

            foreach ($namespaces as $namespace) {
                $class = trim($namespace, '\\') . '\\' . $name;

                if ($base !== null && !is_subclass_of($class, $base)) {
                    continue;
                }

                if (class_exists($class)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \InvalidArgumentException(sprintf("Object instantiation class '%s' not found.", $name));
            }
        }

        $reflector = new \ReflectionClass($class);
        $object = $reflector->newInstanceArgs($args);

        $this->inject($object);

        return $object;
    }

    /**
     * Create object by mixed configuration data
     *
     * @param mixed       $config Configuration data
     * @param string|null $base   Base class
     * @param array       $args   Constructor arguments
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function create($config = null, $base = null, $args = array())
    {
        if (is_string($config)) {
            $config = new self(array('class' => $config));
        } elseif (is_array($config)) {
            $config = new self($config);
        } elseif (!$config instanceof self) {
            throw new \InvalidArgumentException("Object creation data is invalid.");
        }

        $object = $config->instantiate($base, $args);

        return $object;
    }
}