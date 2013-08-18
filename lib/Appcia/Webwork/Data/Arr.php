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
}