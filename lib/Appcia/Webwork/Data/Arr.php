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
     * Check whether two arrays have same keys
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return bool
     */
    public static function equalKeys($arr1, $arr2)
    {
        return !array_diff_key($arr1, $arr2) && !array_diff_key($arr2, $arr1);
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
     * Containment checker
     *
     * @param mixed $value Value
     * @param mixed $set   Set
     *
     * @return boolean
     */
    public static function contains($value, $set)
    {
        if (Value::isEmpty($value) || !Value::isArray($set)) {
            return false;
        }

        if (is_array($set)) {
            return in_array($value, $set);
        } else {
            foreach ($set as $val) {
                if ($value == $val) {
                    return true;
                }
            }
        }

        return false;
    }
}