<?

namespace Appcia\Webwork\Data;

abstract class Arr
{
    /**
     * Create nested array from flat
     * Creates a branch, merging with another produces tree
     *
     * @param array $arr Flat 1D array
     *
     * @return array
     */
    public static function nest($arr)
    {
        $res = array();
        $curr = & $res;

        foreach ($arr as $val) {
            if ($val === end($arr)) {
                $curr = $val;
            } else {
                $curr[$val] = array();
                $curr = & $curr[$val];
            }
        }

        return $res;
    }

    /**
     * Calculate depth
     *
     * @param array $data Data
     *
     * @return int
     */
    public static function depth($data)
    {
        if (!is_array($data)) {
            return 0;
        }

        $max = 1;
        foreach ($data as $value) {
            if (is_array($value)) {
                $depth = static::depth($value) + 1;

                if ($depth > $max) {
                    $max = $depth;
                }
            }
        }

        return $max;
    }

    /**
     * Get array value by key even it does not exist
     *
     * @param array $arr     Array
     * @param mixed $key     Key
     * @param mixed $default Default value
     *
     * @return null
     */
    public static function value(array $arr, $key, $default = null)
    {
        return array_key_exists($key, $arr)
            ? $arr[$key]
            : $default;
    }

    /**
     * Check whether data can be used in 'foreach' loop
     *
     * @param mixed $data Data
     *
     * @return boolean
     */
    public static function traversable($data)
    {
        return is_array($data) || ($data instanceof \Traversable);
    }
}