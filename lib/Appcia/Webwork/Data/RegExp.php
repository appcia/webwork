<?

namespace Appcia\Webwork\Data;

abstract class RegExp
{
    const WILDCARD = '*';

    /**
     * Fetch string with wildcards for simplified production of regular expressions
     *
     * @param string $route Route name with wildcards ('*')
     *
     * @return string
     */
    public static function wildcard($route)
    {
        $parts = explode(static::WILDCARD, $route);
        foreach ($parts as $p => $part) {
            $parts[$p] = preg_quote($part);
        }
        $regexp = '/^' . implode('(.*)', $parts) . '$/';

        return $regexp;
    }
}