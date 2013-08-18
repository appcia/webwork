<?

namespace Appcia\Webwork\Data;

abstract class Value
{
    /**
     * Check whether value seems to be empty
     *
     * @param $value
     *
     * @return boolean
     */
    public static function isEmpty($value)
    {
        $flag = in_array($value, array(null, false, '', array()), true);

        return $flag;
    }

    /**
     * Check whether values could be iterated with foreach loop, accessed like an array
     *
     * @param mixed $value Value
     *
     * @return boolean
     */
    public static function isArray($value)
    {
        $flag = is_array($value)
            || (($value instanceof \Traversable) && ($value instanceof \ArrayAccess));

        return $flag;
    }

    /**
     * Get value treated as string
     *
     * @param mixed $value Value
     *
     * @return string|null
     */
    public static function getString($value)
    {
        $flag = !(!is_scalar($value)
            && !(is_object($value) && method_exists($value, '__toString')));

        if ($flag) {
            $value = (string) $value;
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Check whether value could be a string
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isString($value)
    {
        $flag = (static::getString($value) !== null);

        return $flag;
    }

    /**
     * Get timestamp from various arguments
     *
     * @param mixed $value
     *
     * @return \DateTime
     */
    public static function getDate($value)
    {
        if ($value !== null && !$value instanceof \DateTime) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * Check whether value could be a date
     *
     * @param $value
     *
     * @return bool
     */
    public static function isDate($value)
    {
        $flag = static::getDate($value) !== null;

        return $flag;
    }
}