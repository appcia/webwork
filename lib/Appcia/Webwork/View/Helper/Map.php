<?

namespace Appcia\Webwork\View\Helper;

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
        if (!is_callable($callback)
            || !is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            return array();
        }

        foreach ($data as $key => $value) {
            $data[$key] = call_user_func_array($callback, array($key, $value));
        }

        return $data;
    }
}
