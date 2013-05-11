<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\View\Helper;

class Type extends Helper
{
    const BOOL = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const STRING = 'string';
    const ARR = 'array';
    const OBJECT = 'object';
    const RESOURCE = 'resource';
    const NULL = 'NULL';
    const UNKNOWN = 'unknown';

    /**
     * Caller
     *
     * @param mixed $value Value
     *
     * @return string
     */
    public function type($value)
    {
        $type = gettype($value);

        if ($type == 'double') {
            $type = self::FLOAT;
        } elseif ($type == 'unknown type') {
            $type = self::UNKNOWN;
        }

        return $type;
    }
}
