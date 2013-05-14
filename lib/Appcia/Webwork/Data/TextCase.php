<?

namespace Appcia\Webwork\Data;

class TextCase
{

    /**
     * Convert camel cased text to dashed
     *
     * @param string $str        String to be parsed
     * @param bool   $firstUpper Uppercase first letter?
     *
     * @return string
     */
    public function camelToDashed($str, $firstUpper = false)
    {
        $str = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $str));
        $str = $firstUpper ? ucfirst($str) : lcfirst($str);

        return $str;
    }
}