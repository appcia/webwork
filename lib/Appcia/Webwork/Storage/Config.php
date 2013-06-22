<?

namespace Appcia\Webwork\Storage;
use Appcia\Webwork\Storage\Config\Reader;
use Appcia\Webwork\Storage\Config\Writer;
use Appcia\Webwork\System\File;

/**
 * Aggregator for related data
 *
 * Loader for native arrays placed in PHP files
 * Allows injecting into objects
 *
 * @package Appcia\Webwork\Storage
 */
class Config implements \Iterator, \ArrayAccess
{
    /**
     * Data container
     *
     * @var array
     */
    private $data;

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
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Load data from supported source
     * Reader is determined automatically
     *
     * @param string $source Source
     *
     * @return $this
     * @throws \ErrorException
     */
    public function load($source)
    {
        $reader = Reader::create($source);
        $config = $reader->read($source);

        $this->extend($config);

        return $this;
    }

    /**
     * Save data to supported target
     * Writer is determined automatically
     *
     * @param mixed $target
     *
     * @return $this
     */
    public function save($target)
    {
        $writer = Writer::create($target);
        $writer->write($this, $target);

        return $this;
    }

    /**
     * Merge with another config
     *
     * @param Config $config
     *
     * @return $this
     */
    public function extend(Config $config)
    {
        $this->data = $this->merge($this->data, $config->getData());

        return $this;
    }

    /**
     * Merge two arrays recursive
     *
     *
     * Overwrite values with associative keys
     * Append values with integer keys
     *
     * @param array $arr1 First array
     * @param array $arr2 Second array
     *
     * @return array
     */
    private function merge(array $arr1, array $arr2)
    {
        if (empty($arr1)) {
            return $arr2;
        } else if (empty($arr2)) {
            return $arr1;
        }

        foreach ($arr2 as $key => $value) {
            if (is_int($key)) {
                $arr1[] = $value;
            } elseif (is_array($arr2[$key])) {
                if (!isset($arr1[$key])) {
                    $arr1[$key] = array();
                }

                if (is_int($key)) {
                    $arr1[] = static::merge($arr1[$key], $value);
                } else {
                    $arr1[$key] = static::merge($arr1[$key], $value);
                }
            } else {
                $arr1[$key] = $value;
            }
        }

        return $arr1;
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
        $this->data = $data;

        return $this;
    }

    /**
     * Add data
     *
     * @param array $data
     *
     * @return $this
     */
    public function addData(array $data)
    {
        $this->data = $this->merge($this->data, $data);

        return $this;
    }

    /**
     * Get flattened data
     * Keys are concatenated using '.'
     *
     * @param string $glue Multidimensional key glue
     *
     * @return array
     */
    public function flatten($glue = '.')
    {
        $data = $this->flattenRecursive($this->data, '', $glue);

        return $data;
    }

    /**
     * Recursive helper for data flattening
     *
     * @param array  $array  Data
     * @param string $prefix Key prefix
     * @param string $glue   Key glue
     *
     * @return array
     */
    private function flattenRecursive($array, $prefix, $glue)
    {
        $result = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenRecursive($value, $prefix . $key . $glue, $glue));
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get section data even it does not exist
     * Useful for injecting when values are not specified in config file
     *
     * @param string $key Key in dot notation
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function grab($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Config key cannot be empty.');
        }

        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                return new self();
            }

            $data = & $data[$section];
        }

        if (!is_array($data)) {
            throw new \LogicException(sprintf(
                "Config key '%s' indicates a value but not a section as expected.", $key
            ));
        }

        $section = new self($data);

        return $section;
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
            throw new \InvalidArgumentException('Config property names should be passed as an array.');
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
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Check whether key exists
     *
     * @param string $key Key in dot notation
     *
     * @return boolean
     */
    public function has($key)
    {
        if (empty($key)) {
            return false;
        }

        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                return false;
            }

            $data = & $data[$section];
        }

        return true;
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
     * @param string $key Key in dot notation
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Config key cannot be empty.');
        }

        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                throw new \InvalidArgumentException(sprintf("Config key '%s' does not exist.", $key));
            }

            $data = & $data[$section];
        }

        return $data;
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
     * @param string $key   Key in dot notation
     * @param mixed  $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function set($key, $value)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Config key cannot be empty.');
        }

        $data = & $this->data;

        $sections = explode('.', $key);
        $count = count($sections);

        $s = 0;
        foreach ($sections as $section) {
            $s++;

            if (!isset($data[$section])) {
                $data[$section] = array();
            } else if ($s < $count && !is_array($data[$section])) {
                throw new \LogicException(sprintf(
                    "Config section '%s' in key '%s' indicates a value.",
                    $section,
                    $key
                ));
            }

            $data = & $data[$section];
        }

        $data = $value;

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
     * @param string $key Key in dot notation
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function remove($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Config key cannot be empty.');
        }

        $data = & $this->data;

        $sections = explode('.', $key);
        $count = count($sections);

        $s = 0;
        foreach ($sections as $section) {
            $s++;

            if (!isset($data[$section])) {
                throw new \InvalidArgumentException(sprintf(
                    "Config section '%s' in key '%s' does not exist.", $section, $key
                ));
            }

            if ($s === $count) {
                unset($data[$section]);
                break;
            }

            $data = & $data[$section];
        }

        return $this;
    }
}