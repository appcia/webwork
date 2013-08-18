<?

namespace Appcia\Webwork\View\Helper;

use Appcia\Webwork\Data\Value;
use Appcia\Webwork\View\Helper;

class Map extends Helper
{
    /**
     * Caller
     *
     * @param mixed    $data     Data
     * @param \Closure $callback Callback
     *
     * @return mixed
     */
    public function map($data, \Closure $callback)
    {
        if (!Value::isArray($data)) {
            return array();
        } elseif (!is_callable($callback)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $data[$key] = call_user_func_array($callback, array($key, $value));
        }

        return $data;
    }
}
