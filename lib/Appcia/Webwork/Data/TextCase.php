<?

namespace Appcia\Webwork\Data;

class TextCase
{
    /**
     * Convert camel cased text to dashed
     *
     * @param string $value      Text
     * @param bool   $firstUpper Uppercase first letter
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
     * @param string $value      Text
     * @param bool   $firstUpper Uppercase first letter
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