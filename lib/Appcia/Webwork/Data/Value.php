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

    /**
     * Test some condition
     *
     * @param bool  $flag    Evaluated condition
     * @param mixed $arg1    Returned if value is empty and second argument is not specified
     * @param mixed $arg2    Returned if 2 arguments specified and value is empty
     * @param mixed $default Default value
     *
     * @internal param mixed $value Value to be checked
     * @return mixed
     */
    public static function test($flag, $arg1 = null, $arg2 = null, $default = null)
    {
        if ($arg1 === null && $arg2 === null) {
            return $flag;
        }

        if ($flag) {
            if ($arg2 !== null) {
                return $arg2;
            } else {
                return $arg1;
            }
        }

        return $default;
    }

    /**
     * Check whether text is empty
     *
     * @param mixed $value Value to be checked
     * @param mixed $arg1  Returned if value is empty and second argument is not specified
     * @param mixed $arg2  Returned if 2 arguments specified and value is empty
     *
     * @return mixed
     */
    public static function blank($value, $arg1 = null, $arg2 = null)
    {
        $blank = (mb_strlen($value) === 0);

        return static::test($blank, $arg1, $arg2, $value);
    }

    /**
     * Compare two strings
     *
     * @see test()
     */
    public static function compare($value, $compare, $arg1 = null, $arg2 = null)
    {
        $same = (trim($value) == trim($compare));

        return static::test($same, $arg1, $arg2);
    }
}