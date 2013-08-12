<?

namespace Appcia\Webwork\Data;

/**
 * Text converter
 *
 * @package Appcia\Webwork\Data
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
    public function camelToDashed($value, $firstUpper = false)
    {
        $value = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value));
        $value = $firstUpper ? ucfirst($value) : lcfirst($value);

        return $value;
    }

    /**
     * Convert dashed text to camel cased
     *
     * @param string  $value      Text
     * @param boolean $firstUpper Uppercase first letter
     *
     * @return string
     */
    public function dashedToCamel($value, $firstUpper = false)
    {
        $value = str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
        $value = $firstUpper ? ucfirst($value) : lcfirst($value);

        return $value;
    }
}