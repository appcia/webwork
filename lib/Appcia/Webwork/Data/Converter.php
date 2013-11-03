<?

namespace Appcia\Webwork\Data;

/**
 * Text converter
 */
class Converter
{
    /**
     * Convert camel cased text to dashed
     *
     * @param string  $value      Text
     * @param boolean $firstUpper Uppercase first letter
     *
     * @return string
     */
    public static function camelToDashed($value, $firstUpper = false)
    {
        $dashed = mb_strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
        $dashed = $firstUpper ? ucfirst($dashed) : lcfirst($dashed);

        return $dashed;
    }

    /**
     * Convert dashed text to camel cased
     *
     * @param string  $value      Text
     * @param boolean $firstUpper Uppercase first letter
     *
     * @return string
     */
    public static function dashedToCamel($value, $firstUpper = false)
    {
        $camel = str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
        $camel = $firstUpper ? ucfirst($camel) : lcfirst($camel);

        return $camel;
    }
}