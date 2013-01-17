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
     * @param array $data
     *
     * @return Config
     */
    public function setData($data)
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
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function loadFile($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf("Config file not exists: '%s'", $file));
        }

        $data = @include($file);
        if ($data === false) {
            throw new \LogicException("Cannot load values from config");
        }

        $this->extend(new self($data));

        return $this;
    }

    /**
     * Get value from config
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return new Config();
        }

        if (is_array($this->data[$key])) {
            return new static($this->data[$key]);
        } else {
            return $this->data[$key];
        }
    }

    /**
     * Set value in config
     *
     * @param $key
     * @param $value
     *
     * @return Config
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

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
     * @param Object $obj      Target object
     * @param array  $required Required parameters
     *
     * @return Config
     * @throws \ErrorException
     */
    public function inject($obj, $required = array())
    {
        foreach ($this->data as $key => $value) {
            $callback = array($obj, 'set' . ucfirst($key));

            if (is_callable($callback)) {
                call_user_func($callback, $value);
            } else if (in_array($key, $required)) {
                throw new \ErrorException("Config injection error. Required parameter '%s' cannot be passed", $key);
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
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
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