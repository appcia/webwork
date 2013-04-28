<?

namespace Appcia\Webwork;

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
     * @param array $data
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
     * @param string $file Path to file to be loaded
     *
     * @return Config
     * @throws Exception
     */
    public function loadFile($file)
    {
        if (!file_exists($file)) {
            throw new Exception(sprintf("Config file not exists: '%s'", $file));
        }

        $data = @include($file);
        if ($data === false) {
            throw new Exception("Cannot load values from config");
        }

        $this->extend(new self($data));

        return $this;
    }

    /**
     * Get value from config
     *
     * @param string $key Key with sections, dot as separator
     *
     * @return mixed
     */
    public function get($key)
    {
        $data = & $this->data;
        foreach (explode('.', $key) as $section) {
            if (!isset($data[$section])) {
                $data = null;
                break;
            }

            $data = & $data[$section];
        }

        if (is_array($data)) {
            return new static($data);
        } else {
            return $data;
        }
    }

    /**
     * Set value in config
     *
     * @param string $key   Key with sections, dot as separator
     * @param mixed  $value Value
     *
     * @return Config
     * @throws Exception
     */
    public function set($key, $value)
    {
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
                    "Config section '%s' in key '%s' cannot indicate a value",
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
     * Get data as native array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Inject config by object setters automagically
     *
     * @param Object $object Target object
     *
     * @return Config
     */
    public function inject($object)
    {
        foreach ($this->data as $property => $value) {
            $callback = array($object, 'set' . ucfirst($property));

            if (is_callable($callback)) {
                call_user_func($callback, $value);
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
        $this->data = $this->merge($this->data, $config->toArray());

        return $this;
    }

    /**
     * Merge two arrays recursive
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