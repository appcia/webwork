<?

namespace Appcia\Webwork\Storage;

use Appcia\Webwork\Exception\Exception;

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
     * Set data
     *
     * @param array $data Data
     *
     * @return Config
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
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
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Load from file
     *
     * @param string $path Path
     *
     * @return Config
     * @throws Exception
     */
    public function loadFile($path)
    {
        if (!file_exists($path)) {
            throw new Exception(sprintf("Config file not exists: '%s'", $path));
        }

        // Possible syntax errors, do not handle them / expensive!
        $data = include($path);

        if (!is_array($data)) {
            throw new Exception("Config file should return array: '%s'", $path);
        }

        $this->extend(new self($data));

        return $this;
    }

    /**
     * Check whether key exists
     *
     * @param string $key Key in dot notation
     *
     * @return bool
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
     * Get value
     *
     * @param string $key Key in dot notation
     *
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        if (empty($key)) {
            throw new Exception('Config key cannot be empty');
        }

        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                throw new Exception(sprintf("Config key '%s' does not exist", $key));
            }

            $data = & $data[$section];
        }

        return $data;
    }

    /**
     * Set value
     *
     * @param string $key   Key in dot notation
     * @param mixed  $value Value
     *
     * @return Config
     * @throws Exception
     */
    public function set($key, $value)
    {
        if (empty($key)) {
            throw new Exception('Config key cannot be empty');
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
                throw new Exception(sprintf(
                    "Config section '%s' in key '%s' indicates a value",
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
     * Get section data even it does not exist
     * Useful for injecting when values are not specified in config file
     *
     * @param string $key Key in dot notation
     *
     * @return Config
     * @throws Exception
     */
    public function grab($key)
    {
        if (empty($key)) {
            throw new Exception('Config key cannot be empty');
        }

        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                return new self();
            }

            $data = & $data[$section];
        }

        if (!is_array($data)) {
            throw new Exception(sprintf("Config key '%s' indicates a value but not a section as expected", $key));
        }

        $section = new self($data);

        return $section;
    }

    /**
     * Inject data by object setters
     *
     * @param object $object Target object
     *
     * @return Config
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
     * @return Config
     * @throws Exception
     */
    public function suck($object, $properties = null)
    {
        if ($properties === null) {
            $properties = get_object_vars($object);
        } elseif (!is_array($properties)) {
            throw new Exception('Config property names should be passed as an array');
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
     * Merge with another config
     *
     * @param Config $config
     *
     * @return Config
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
    public static function merge(array $arr1, array $arr2)
    {
        if (empty($arr1)) {
            return $arr2;
        } else if (empty($arr2)) {
            return $arr1;
        }

        foreach ($arr2 as $key => $value) {
            if (is_array($value)) {
                if (!isset($arr1[$key])) {
                    $arr1[$key] = array();
                }

                if (is_int($key)) {
                    $arr1[] = static::merge($arr1[$key], $arr2[$key]);
                } else {
                    $arr1[$key] = static::merge($arr1[$key], $arr2[$key]);
                }
            } else {
                $arr1[$key] = $value;
            }
        }

        return $arr1;
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

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);

        return $this;
    }
}