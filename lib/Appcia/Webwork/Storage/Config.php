<?

namespace Appcia\Webwork\Storage;

use Appcia\Webwork\Core\Objector;
use Appcia\Webwork\Storage\Config\Reader;
use Appcia\Webwork\Storage\Config\Writer;

/**
 * Aggregator for related data
 *
 * Loader for native arrays placed in PHP files
 * Allows injecting into objects
 *
 * @package Appcia\Webwork\Storage
 */
class Config extends Objector
{
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
        $reader = Reader::objectify($source);
        $config = $reader->read($source);

        $this->extend($config);

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
    protected function merge(array $arr1, array $arr2)
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
     * Save data to supported target
     * Writer is determined automatically
     *
     * @param mixed $target
     *
     * @return $this
     */
    public function save($target)
    {
        $writer = Writer::objectify($target);
        $writer->write($this, $target);

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
    protected function flattenRecursive($array, $prefix, $glue)
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
     * Get a value
     *
     * @param string $key     Key in dot notation
     * @param mixed  $default Value if key not found
     *
     * @throws \InvalidArgumentException
     * @return self|$this
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Config key cannot be empty.');
        }

        $data = & $this->data;
        $sections = explode('.', $key);

        foreach ($sections as $section) {
            if (!is_array($data) || !array_key_exists($section, $data)) {
                return $default;
            }

            $data = & $data[$section];
        }

        $value = is_array($data)
            ? new static($data)
            : $data;

        return $value;
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

    /**
     * Allows iterating with foreach loop
     * Make nested configs from array values
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $data = $this->data;
        $data = array_map(function ($value) {
            return is_array($value)
                ? new static($value)
                : $value;
        }, $data);

        return new \ArrayIterator($data);
    }
}